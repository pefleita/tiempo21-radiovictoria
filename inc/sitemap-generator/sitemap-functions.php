<?php
/**
 * Funciones auxiliares para el generador de sitemaps
 * 
 * @package Sitemap_Generator
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Inicializar el generador de sitemaps
 */
function sitemap_generator_init() {
    static $generator = null;
    
    if (null === $generator) {
        $generator = new Sitemap_Generator();
    }
    
    return $generator;
}

// Inicializar el generador inmediatamente para registrar los hooks
sitemap_generator_init();

/**
 * Añadir sitemap al robots.txt
 */
function sitemap_add_to_robots_txt($output) {
    $sitemap_url = home_url('/sitemap.xml');
    $output .= "\n# Sitemap\n";
    $output .= "Sitemap: " . $sitemap_url . "\n";
    
    return $output;
}
add_filter('robots_txt', 'sitemap_add_to_robots_txt', 10, 1);

/**
 * Añadir enlace al sitemap en el admin
 */
function sitemap_admin_bar_submenu($wp_admin_bar) {
    if (!is_admin_bar_showing() || !current_user_can('manage_options')) {
        return;
    }

    $sitemap_url = home_url('/sitemap.xml');

    $wp_admin_bar->add_node(array(
        'parent' => 'site-name',
        'id'     => 'sitemap-view',
        'title'  => 'Ver Sitemap XML',
        'href'   => $sitemap_url,
        'meta'   => array(
            'target' => '_blank',
            'title'  => 'Abrir sitemap XML'
        )
    ));
}
add_action('admin_bar_menu', 'sitemap_admin_bar_submenu', 80);

/**
 * Función para regenerar manualmente los sitemaps
 */
function regenerate_sitemaps_manual() {
    $generator = sitemap_generator_init();
    $generator->regenerate_all_sitemaps();
    
    return 'Sitemaps regenerados exitosamente';
}

/**
 * Shortcode para mostrar el enlace al sitemap
 */
function sitemap_shortcode($atts) {
    $atts = shortcode_atts(array(
        'text' => 'Mapa del sitio XML',
        'class' => 'sitemap-link'
    ), $atts);
    
    $sitemap_url = home_url('/sitemap.xml');
    
    return sprintf(
        '<a href="%s" class="%s" target="_blank">%s</a>',
        esc_url($sitemap_url),
        esc_attr($atts['class']),
        esc_html($atts['text'])
    );
}
add_shortcode('sitemap_link', 'sitemap_shortcode');
