<?php
/**
 * Clase principal para generación de sitemaps XML estáticos
 * 
 * @package Sitemap_Generator
 */

if (!defined('ABSPATH')) {
    exit;
}

class Sitemap_Generator {
    
    private $max_urls_per_sitemap = 1000;
    private $sitemap_update_interval = 3600; // 1 hora por defecto
    private $base_dir;
    private $base_url;
    
    /**
     * Constructor
     */
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->base_dir = trailingslashit($upload_dir['basedir']) . 'sitemaps/';
        $this->base_url = trailingslashit($upload_dir['baseurl']) . 'sitemaps/';
        
        $this->init_hooks();
        $this->ensure_directory_exists();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        add_action('init', array($this, 'register_rewrite_rules'));
        add_action('template_redirect', array($this, 'serve_sitemap'));
        
        // Invalidar caché al publicar/editar
        add_action('save_post', array($this, 'maybe_regenerate_sitemap'), 10, 2);
        add_action('delete_post', array($this, 'invalidate_sitemap_cache'));
        add_action('publish_to_trash', array($this, 'invalidate_sitemap_cache'));
        
        // Programar regeneration periódica
        if (!wp_next_scheduled('t21_sitemap_scheduled_update')) {
            wp_schedule_event(time(), 'hourly', 't21_sitemap_scheduled_update');
        }
        add_action('t21_sitemap_scheduled_update', array($this, 'regenerate_all_sitemaps'));
    }
    
    /**
     * Asegurar que el directorio de sitemaps existe
     */
    private function ensure_directory_exists() {
        if (!file_exists($this->base_dir)) {
            wp_mkdir_p($this->base_dir);
            $this->add_htaccess_protection();
        }
    }
    
    /**
     * Añadir protección .htaccess al directorio
     */
    private function add_htaccess_protection() {
        $htaccess_file = $this->base_dir . '.htaccess';
        
        if (!file_exists($htaccess_file)) {
            $content = "Options -Indexes\n";
            $content .= "<Files ~ \"\.xml$\">\n";
            $content .= "    Order allow,deny\n";
            $content .= "    Allow from all\n";
            $content .= "</Files>\n";
            
            file_put_contents($htaccess_file, $content);
        }
    }
    
    /**
     * Registrar rewrite rules para los sitemaps
     */
    public function register_rewrite_rules() {
        add_rewrite_rule('^sitemap\.xml$', 'index.php?sitemap=index', 'top');
        add_rewrite_rule('^sitemap-([a-z]+)-([0-9]+)\.xml$', 'index.php?sitemap=$matches[1]&sitemap_page=$matches[2]', 'top');
        
        add_rewrite_tag('%sitemap%', '([^&]+)');
        add_rewrite_tag('%sitemap_page%', '([0-9]+)');
    }
    
    /**
     * Servir el sitemap solicitado
     */
    public function serve_sitemap() {
        $sitemap = get_query_var('sitemap');
        
        if (!$sitemap) {
            return;
        }
        
        $page = intval(get_query_var('sitemap_page')) ?: 1;
        
        switch ($sitemap) {
            case 'index':
                $this->serve_main_sitemap();
                break;
            case 'posts':
                $this->serve_posts_sitemap($page);
                break;
            case 'pages':
                $this->serve_pages_sitemap($page);
                break;
            default:
                status_header(404);
                exit;
        }
    }
    
    /**
     * Servir sitemap principal (índice)
     */
    private function serve_main_sitemap() {
        $this->serve_xml_file('sitemap-index.xml', 'main');
    }
    
    /**
     * Servir sitemap de posts
     */
    private function serve_posts_sitemap($page) {
        $this->serve_xml_file("sitemap-posts-{$page}.xml", 'posts', $page);
    }
    
    /**
     * Servir sitemap de páginas
     */
    private function serve_pages_sitemap($page) {
        $this->serve_xml_file("sitemap-pages-{$page}.xml", 'pages', $page);
    }
    
    /**
     * Servir archivo XML estático
     */
    private function serve_xml_file($filename, $type = '', $page = 1) {
        $filepath = $this->base_dir . $filename;
        
        // Si el archivo no existe o está obsoleto, regenerarlo
        if (!file_exists($filepath) || $this->is_sitemap_stale($filepath, $type)) {
            $this->generate_sitemap($type, $page);
        }
        
        if (file_exists($filepath)) {
            header('Content-Type: application/xml; charset=utf-8');
            header('X-Robots-Tag: noindex, follow', true);
            readfile($filepath);
            exit;
        }
        
        // Si falla, generar sobre la marcha
        $this->generate_sitemap($type, $page);
        if (file_exists($filepath)) {
            header('Content-Type: application/xml; charset=utf-8');
            header('X-Robots-Tag: noindex, follow', true);
            readfile($filepath);
            exit;
        }
        
        status_header(404);
        exit;
    }
    
    /**
     * Verificar si el sitemap está obsoleto
     */
    private function is_sitemap_stale($filepath, $type) {
        if (!file_exists($filepath)) {
            return true;
        }
        
        $filemtime = filemtime($filepath);
        return (time() - $filemtime) > $this->sitemap_update_interval;
    }
    
    /**
     * Generar sitemap específico
     */
    private function generate_sitemap($type, $page = 1) {
        switch ($type) {
            case 'main':
                return $this->generate_main_sitemap();
            case 'posts':
                return $this->generate_posts_sitemap($page);
            case 'pages':
                return $this->generate_pages_sitemap($page);
        }
    }
    
    /**
     * Regenerar todos los sitemaps
     */
    public function regenerate_all_sitemaps() {
        $this->generate_main_sitemap();
        
        $post_count = $this->get_post_count('post');
        $post_sitemaps = ceil($post_count / $this->max_urls_per_sitemap);
        for ($i = 1; $i <= $post_sitemaps; $i++) {
            $this->generate_posts_sitemap($i);
        }
        
        $page_count = $this->get_post_count('page');
        $page_sitemaps = ceil($page_count / $this->max_urls_per_sitemap);
        for ($i = 1; $i <= $page_sitemaps; $i++) {
            $this->generate_pages_sitemap($i);
        }
    }
    
    /**
     * Regenerar si es necesario (hook de save_post)
     */
    public function maybe_regenerate_sitemap($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!in_array($post->post_status, array('publish', 'trash'))) {
            return;
        }
        
        // Regenerar solo el índice y el primer sitemap de posts (los más recientes)
        $this->generate_main_sitemap();
        $this->generate_posts_sitemap(1);
    }
    
    /**
     * Generar sitemap principal (índice)
     */
    private function generate_main_sitemap() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Sitemaps de posts
        $post_count = $this->get_post_count('post');
        $post_sitemaps = max(1, ceil($post_count / $this->max_urls_per_sitemap));
        
        for ($i = 1; $i <= $post_sitemaps; $i++) {
            $xml .= $this->build_sitemap_entry('posts', $i);
        }
        
        // Sitemaps de páginas
        $page_count = $this->get_post_count('page');
        $page_sitemaps = max(1, ceil($page_count / $this->max_urls_per_sitemap));
        
        for ($i = 1; $i <= $page_sitemaps; $i++) {
            $xml .= $this->build_sitemap_entry('pages', $i);
        }
        
        $xml .= '</sitemapindex>';
        
        $this->save_xml_file('sitemap-index.xml', $xml);
        return $xml;
    }
    
    /**
     * Construir entrada individual del sitemap
     */
    private function build_sitemap_entry($type, $page) {
        $url = home_url("/sitemap-{$type}-{$page}.xml");
        $filepath = $this->base_dir . "sitemap-{$type}-{$page}.xml";
        $lastmod = file_exists($filepath) ? date('c', filemtime($filepath)) : date('c');
        
        $entry = "\t<sitemap>\n";
        $entry .= "\t\t<loc>" . esc_url($url) . "</loc>\n";
        $entry .= "\t\t<lastmod>" . esc_html($lastmod) . "</lastmod>\n";
        $entry .= "\t</sitemap>\n";
        
        return $entry;
    }
    
    /**
     * Generar sitemap de posts
     */
    private function generate_posts_sitemap($page) {
        $args = array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $this->max_urls_per_sitemap,
            'offset'         => ($page - 1) * $this->max_urls_per_sitemap,
            'orderby'        => 'modified',
            'order'          => 'DESC',
            'no_found_rows'  => false,
        );
        
        $query = new WP_Query($args);
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $xml .= $this->build_url_entry(get_the_ID());
            }
            wp_reset_postdata();
        }
        
        $xml .= '</urlset>';
        
        $this->save_xml_file("sitemap-posts-{$page}.xml", $xml);
        return $xml;
    }
    
    /**
     * Generar sitemap de páginas
     */
    private function generate_pages_sitemap($page) {
        $args = array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => $this->max_urls_per_sitemap,
            'offset'         => ($page - 1) * $this->max_urls_per_sitemap,
            'orderby'        => 'modified',
            'order'          => 'DESC',
            'no_found_rows'  => false,
        );
        
        $query = new WP_Query($args);
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $xml .= $this->build_url_entry(get_the_ID());
            }
            wp_reset_postdata();
        }
        
        $xml .= '</urlset>';
        
        $this->save_xml_file("sitemap-pages-{$page}.xml", $xml);
        return $xml;
    }
    
    /**
     * Construir entrada de URL individual
     */
    private function build_url_entry($post_id) {
        $url = get_permalink($post_id);
        $modified = get_the_modified_time('c', $post_id);
        
        // Determinar frecuencia de cambio basada en la antigüedad del post
        $post_age = time() - get_the_modified_time('U', $post_id);
        $changefreq = 'weekly';
        
        if ($post_age < DAY_IN_SECONDS * 7) {
            $changefreq = 'daily';
        } elseif ($post_age < DAY_IN_SECONDS * 30) {
            $changefreq = 'weekly';
        } else {
            $changefreq = 'monthly';
        }
        
        // Determinar prioridad
        $priority = 0.6;
        
        if ($post_age < DAY_IN_SECONDS * 2) {
            $priority = 1.0;
        } elseif ($post_age < DAY_IN_SECONDS * 7) {
            $priority = 0.8;
        } elseif ($post_age < DAY_IN_SECONDS * 30) {
            $priority = 0.7;
        }
        
        $entry = "\t<url>\n";
        $entry .= "\t\t<loc>" . esc_url($url) . "</loc>\n";
        $entry .= "\t\t<lastmod>" . esc_html($modified) . "</lastmod>\n";
        $entry .= "\t\t<changefreq>" . esc_html($changefreq) . "</changefreq>\n";
        $entry .= "\t\t<priority>" . esc_html($priority) . "</priority>\n";
        $entry .= "\t</url>\n";
        
        return $entry;
    }
    
    /**
     * Guardar archivo XML
     */
    private function save_xml_file($filename, $content) {
        $filepath = $this->base_dir . $filename;
        
        // Comprimir con gzip si es posible
        if (function_exists('gzencode')) {
            $gzfilepath = $filepath . '.gz';
            file_put_contents($gzfilepath, gzencode($content, 6));
        }
        
        return file_put_contents($filepath, $content);
    }
    
    /**
     * Obtener conteo de posts por tipo
     */
    private function get_post_count($post_type) {
        return wp_count_posts($post_type)->publish ?: 0;
    }
    
    /**
     * Invalidar caché de sitemaps (limpiar archivos)
     */
    public function invalidate_sitemap_cache() {
        // Eliminar archivos de sitemap
        $files = glob($this->base_dir . 'sitemap-*.xml');
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
        
        // Eliminar versiones gzip
        $gzfiles = glob($this->base_dir . 'sitemap-*.xml.gz');
        if ($gzfiles) {
            foreach ($gzfiles as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
        
        // Reforzar rewrite rules
        flush_rewrite_rules(false);
    }
    
    /**
     * Activar el sitemap
     */
    public function activate() {
        $this->register_rewrite_rules();
        flush_rewrite_rules();
        $this->ensure_directory_exists();
        
        // Generar sitemaps iniciales
        $this->regenerate_all_sitemaps();
        
        // Programar actualización periódica
        if (!wp_next_scheduled('t21_sitemap_scheduled_update')) {
            wp_schedule_event(time(), 'hourly', 't21_sitemap_scheduled_update');
        }
    }
    
    /**
     * Desactivar el sitemap
     */
    public function deactivate() {
        wp_clear_scheduled_hook('t21_sitemap_scheduled_update');
        flush_rewrite_rules();
    }
    
    /**
     * Obtener URL del sitemap
     */
    public static function get_sitemap_url() {
        return home_url('/sitemap.xml');
    }
}
