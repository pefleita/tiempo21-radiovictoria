<?php
/**
 * Tiempo21 - Functions
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'T21_VERSION', wp_get_theme()->get( 'Version' ) );
define( 'T21_DIR',     get_template_directory() );
define( 'T21_URI',     get_template_directory_uri() );

/* =========================================
   INCLUDES
   ========================================= */
require_once T21_DIR . '/inc/view-counter.php';
require_once T21_DIR . '/inc/sitemap-generator/config.php';
require_once T21_DIR . '/inc/sitemap-generator/class-sitemap-generator.php';
require_once T21_DIR . '/inc/sitemap-generator/sitemap-functions.php';
require_once T21_DIR . '/inc/sitemap-generator/sitemap-rewrite-rules.php';

/* =========================================
   THEME SETUP
   ========================================= */
function t21_theme_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form','comment-form','comment-list','gallery','caption','script','style' ] );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'customize-selective-refresh-widgets' );

    add_image_size( 'hero-large',   900, 506, true );
    add_image_size( 'card-medium',  600, 338, true );
    add_image_size( 'card-small',   400, 225, true );
    add_image_size( 'thumb-tiny',   120,  80, true );

    register_nav_menus( [
        'primary' => __( 'Menu Principal', 'tiempo21-radiovictoria' ),
        'footer'  => __( 'Menu Footer',    'tiempo21-radiovictoria' ),
        'footer_secondary' => __( 'Menu Footer Secundario', 'tiempo21-radiovictoria' ),
    ] );

    load_theme_textdomain( 'tiempo21-radiovictoria', T21_DIR . '/languages' );
}
add_action( 'after_setup_theme', 't21_theme_setup' );

/* =========================================
   SCRIPTS & STYLES
   ========================================= */
function t21_enqueue_scripts() {
    wp_enqueue_style( 'google-fonts', T21_URI . '/assets/fonts/open-sans.css', [], T21_VERSION );
    wp_enqueue_style( 'font-awesome', T21_URI . '/assets/fonts/fontawesome/css/all.min.css', [], '6.5.1' );
    wp_enqueue_style( 'tiempo21-style', get_stylesheet_uri(), [ 'google-fonts', 'font-awesome' ], T21_VERSION );
    wp_enqueue_script( 'tiempo21-js', T21_URI . '/assets/js/main.js', [], T21_VERSION, true );
    
    wp_localize_script( 'tiempo21-js', 't21Settings', [
        'smoothScroll' => get_theme_mod( 't21_smooth_scroll', true ),
    ] );
}
add_action( 'wp_enqueue_scripts', 't21_enqueue_scripts' );

function t21_remove_image_dimensions( $content ) {
    $content = preg_replace( '/<img([^>]*)\s+width="[^"]*"([^>]*)>/i', '<img$1$2>', $content );
    $content = preg_replace( '/<img([^>]*)\s+height="[^"]*"([^>]*)>/i', '<img$1$2>', $content );
    $content = preg_replace( '/(<figure[^>]*)\s+style="[^"]*width:\s*\d+px[^"]*"([^>]*)>/', '$1$2>', $content );
    $content = preg_replace( '/sizes="auto,\s*/i', 'sizes="', $content );
    return $content;
}
add_filter( 'the_content', 't21_remove_image_dimensions', 20 );

function t21_fix_iframe_display( $content ) {
    if ( false === strpos( $content, '<iframe' ) ) {
        return $content;
    }

    $content = preg_replace( '/<iframe\s+([^>]*)\/>/', '<iframe $1></iframe>', $content );
    $content = preg_replace( '/<p>\s*(\<iframe[^>]*\>.*?\<\/iframe\>)\s*<\/p>/s', '$1', $content );
    $content = preg_replace( '/<p>\s*(\<iframe[^>]*\/>)\s*<\/p>/s', '$1', $content );

    return $content;
}
add_filter( 'the_content', 't21_fix_iframe_display', 30 );

function t21_add_defer_to_scripts( $tag, $handle ) {
    if ( 'tiempo21-js' === $handle ) {
        return str_replace( ' src=', ' defer src=', $tag );
    }
    return $tag;
}
add_filter( 'script_loader_tag', 't21_add_defer_to_scripts', 10, 2 );

/* =========================================
   WIDGETS
   ========================================= */
function t21_widgets_init() {
    register_sidebar( [
        'name'          => __( 'Sidebar de Noticias', 'tiempo21-radiovictoria' ),
        'id'            => 'sidebar-single',
        'description'   => __( 'Sidebar que aparece en las noticias y paginas.', 'tiempo21-radiovictoria' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3><div class="widget-content">',
    ] );

    for ( $i = 1; $i <= 3; $i++ ) {
        register_sidebar( [
            'name'          => sprintf( __( 'Footer Area %d', 'tiempo21-radiovictoria' ), $i ),
            'id'            => 'footer-' . $i,
            'description'   => sprintf( __( 'Area de widgets del footer numero %d.', 'tiempo21-radiovictoria' ), $i ),
            'before_widget' => '<div id="%1$s" class="footer-widget-item %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="footer-widget-title">',
            'after_title'   => '</h4>',
        ] );
    }
}
add_action( 'widgets_init', 't21_widgets_init' );

/* =========================================
   CUSTOMIZER
   ========================================= */
function t21_customize_register( $wp_customize ) {

    // Banner image
    $wp_customize->add_setting( 't21_banner_image', [
        'default'           => '',
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 't21_banner_image', [
        'label'     => __( 'Banner del sitio', 'tiempo21-radiovictoria' ),
        'section'   => 'title_tagline',
        'mime_type' => 'image',
    ] ) );

    // Hide site title (visible to search engines)
    $wp_customize->add_setting( 't21_hide_site_title', [
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 't21_hide_site_title', [
        'label'    => __( 'Ocultar título del sitio (visible para buscadores)', 'tiempo21-radiovictoria' ),
        'section'  => 'title_tagline',
        'type'     => 'checkbox',
    ] );

    // Hide site description (visible to search engines)
    $wp_customize->add_setting( 't21_hide_site_description', [
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 't21_hide_site_description', [
        'label'    => __( 'Ocultar descripción del sitio (visible para buscadores)', 'tiempo21-radiovictoria' ),
        'section'  => 'title_tagline',
        'type'     => 'checkbox',
    ] );

    // Social links
    $wp_customize->add_section( 't21_social', [
        'title'    => __( 'Redes Sociales', 'tiempo21-radiovictoria' ),
        'priority' => 30,
    ] );
    $socials = [
        'facebook'  => 'Facebook URL',
        'twitter'   => 'X / Twitter URL',
        'instagram' => 'Instagram URL',
        'youtube'   => 'YouTube URL',
        'telegram'  => 'Telegram URL',
    ];
    foreach ( $socials as $key => $label ) {
        $wp_customize->add_setting( 't21_social_' . $key, [ 'default' => '', 'sanitize_callback' => 'esc_url_raw' ] );
        $wp_customize->add_control( 't21_social_' . $key, [
            'label'   => $label,
            'section' => 't21_social',
            'type'    => 'url',
        ] );
    }

    // Audio stream
    $wp_customize->add_section( 't21_audio', [
        'title'    => __( 'Radio / Audio en Vivo', 'tiempo21-radiovictoria' ),
        'priority' => 35,
    ] );
    $wp_customize->add_setting( 't21_audio_url',   [ 'default' => '', 'sanitize_callback' => 'esc_url_raw' ] );
    $wp_customize->add_setting( 't21_audio_label', [ 'default' => 'Radio Victoria 1170 AM', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 't21_audio_url',   [ 'label' => 'URL del stream (mp3/m3u8)', 'section' => 't21_audio', 'type' => 'url' ] );
    $wp_customize->add_control( 't21_audio_label', [ 'label' => 'Texto del reproductor', 'section' => 't21_audio', 'type' => 'text' ] );

    // Image links section
    $wp_customize->add_section( 't21_imglinks', [
        'title'    => __( 'Seccion de Imagenes / Links', 'tiempo21-radiovictoria' ),
        'priority' => 40,
    ] );
    for ( $i = 1; $i <= 4; $i++ ) {
        $wp_customize->add_setting( 't21_imglink_img_' . $i,   [ 'default' => '', 'sanitize_callback' => 'absint' ] );
        $wp_customize->add_setting( 't21_imglink_url_' . $i,   [ 'default' => '', 'sanitize_callback' => 'esc_url_raw' ] );
        $wp_customize->add_setting( 't21_imglink_label_' . $i, [ 'default' => 'Enlace ' . $i, 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 't21_imglink_img_' . $i, [
            'label'     => "Imagen $i",
            'section'   => 't21_imglinks',
            'mime_type' => 'image',
        ] ) );
        $wp_customize->add_control( 't21_imglink_url_' . $i,   [ 'label' => "URL $i",   'section' => 't21_imglinks', 'type' => 'url' ] );
        $wp_customize->add_control( 't21_imglink_label_' . $i, [ 'label' => "Texto $i", 'section' => 't21_imglinks', 'type' => 'text' ] );
    }

    // Category sections
    $wp_customize->add_panel( 't21_cats_panel', [
        'title'    => __( 'Secciones de Categorias (Inicio)', 'tiempo21-radiovictoria' ),
        'priority' => 50,
    ] );
    $cat_configs = [
        1 => [ 'name' => 'Categoria 1 (Grande)',       'count' => 5 ],
        2 => [ 'name' => 'Categoria 2 (Mediana)',       'count' => 4 ],
        3 => [ 'name' => 'Categoria 3 (Mediana)',       'count' => 4 ],
        4 => [ 'name' => 'Categoria 4 (Pequena)',       'count' => 3 ],
        5 => [ 'name' => 'Categoria 5 (Pequena)',       'count' => 3 ],
        6 => [ 'name' => 'Categoria 6 (Pequena)',       'count' => 3 ],
        7 => [ 'name' => 'Categoria 7 (Pequena)',       'count' => 3 ],
    ];
    
    $categories = get_categories( [ 'hide_empty' => false ] );
    $cat_choices = [ '' => __( '-- Seleccionar categoria --', 'tiempo21-radiovictoria' ) ];
    foreach ( $categories as $cat ) {
        $cat_choices[ $cat->slug ] = $cat->name;
    }
    
    foreach ( $cat_configs as $n => $cfg ) {
        $wp_customize->add_section( 't21_cat_' . $n, [
            'title' => $cfg['name'],
            'panel' => 't21_cats_panel',
        ] );
        $wp_customize->add_setting( 't21_cat_slug_' . $n,    [ 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_setting( 't21_cat_count_' . $n, [ 'default' => $cfg['count'], 'sanitize_callback' => 'absint' ] );
        $wp_customize->add_setting( 't21_cat_show_image_' . $n, [ 'default' => true, 'sanitize_callback' => 't21_sanitize_checkbox' ] );
        $wp_customize->add_setting( 't21_cat_show_date_' . $n, [ 'default' => true, 'sanitize_callback' => 't21_sanitize_checkbox' ] );
        $wp_customize->add_setting( 't21_cat_first_featured_' . $n, [ 'default' => true, 'sanitize_callback' => 't21_sanitize_checkbox' ] );
        $wp_customize->add_control( 't21_cat_slug_' . $n, [
            'label'   => __( 'Categoria', 'tiempo21-radiovictoria' ),
            'section' => 't21_cat_' . $n,
            'type'    => 'select',
            'choices' => $cat_choices,
        ] );
        $wp_customize->add_control( 't21_cat_count_' . $n, [
            'label'   => __( 'Cantidad de noticias', 'tiempo21-radiovictoria' ),
            'section' => 't21_cat_' . $n,
            'type'    => 'number',
        ] );
        $wp_customize->add_control( 't21_cat_show_image_' . $n, [
            'label'   => __( 'Mostrar imagen', 'tiempo21-radiovictoria' ),
            'section' => 't21_cat_' . $n,
            'type'    => 'checkbox',
        ] );
        $wp_customize->add_control( 't21_cat_show_date_' . $n, [
            'label'   => __( 'Mostrar fecha', 'tiempo21-radiovictoria' ),
            'section' => 't21_cat_' . $n,
            'type'    => 'checkbox',
        ] );
        $wp_customize->add_control( 't21_cat_first_featured_' . $n, [
            'label'   => __( 'Primera noticia con imagen grande', 'tiempo21-radiovictoria' ),
            'section' => 't21_cat_' . $n,
            'type'    => 'checkbox',
        ] );
    }

    // Categoría 1 opciones adicionales de excerpt
    $wp_customize->add_setting( 't21_cat1_show_excerpt', [ 'default' => true, 'sanitize_callback' => 't21_sanitize_checkbox' ] );
    $wp_customize->add_setting( 't21_cat1_excerpt_length', [ 'default' => 50, 'sanitize_callback' => 'absint' ] );
    $wp_customize->add_control( 't21_cat1_show_excerpt', [
        'label'   => __( 'Categoria 1: Mostrar excerpt', 'tiempo21-radiovictoria' ),
        'section' => 't21_cat_1',
        'type'    => 'checkbox',
    ] );
    $wp_customize->add_control( 't21_cat1_excerpt_length', [
        'label'   => __( 'Categoria 1: Palabras del excerpt', 'tiempo21-radiovictoria' ),
        'section' => 't21_cat_1',
        'type'    => 'number',
    ] );

    // Latest & Most Read counts
    $wp_customize->add_section( 't21_hero_counts', [
        'title'    => __( 'Ultimas / Mas leidas (Inicio)', 'tiempo21-radiovictoria' ),
        'priority' => 45,
    ] );
    $wp_customize->add_setting( 't21_latest_count',  [ 'default' => 5, 'sanitize_callback' => 'absint' ] );
    $wp_customize->add_setting( 't21_popular_count', [ 'default' => 5, 'sanitize_callback' => 'absint' ] );
    $wp_customize->add_setting( 't21_latest_show_image', [
        'default'           => true,
        'sanitize_callback' => 't21_sanitize_checkbox',
    ] );
    $wp_customize->add_control( 't21_latest_count',  [ 'label' => 'Cantidad Ultimas Noticias',  'section' => 't21_hero_counts', 'type' => 'number' ] );
    $wp_customize->add_control( 't21_popular_count', [ 'label' => 'Cantidad Mas Leidas',        'section' => 't21_hero_counts', 'type' => 'number' ] );
    $wp_customize->add_control( 't21_latest_show_image', [
        'label'   => __( 'Mostrar imagen en Ultimas Noticias', 'tiempo21-radiovictoria' ),
        'section' => 't21_hero_counts',
        'type'    => 'checkbox',
    ] );

    $wp_customize->add_setting( 't21_photo_cat', [ 'default' => 'fotorreportajes', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 't21_photo_cat', [
        'label'   => __( 'Slug de categoria Fotorreportajes', 'tiempo21-radiovictoria' ),
        'section' => 't21_hero_counts',
        'type'    => 'text',
    ] );

    // Videos section
    $wp_customize->add_section( 't21_videos', [
        'title'    => __( 'Seccion de Videos (Inicio)', 'tiempo21-radiovictoria' ),
        'priority' => 55,
    ] );
    for ( $v = 1; $v <= 3; $v++ ) {
        $wp_customize->add_setting( 't21_video_title_' . $v, [ 'default' => 'Video ' . $v, 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_setting( 't21_video_url_' . $v,   [ 'default' => '', 'sanitize_callback' => 'esc_url_raw' ] );
        $wp_customize->add_control( 't21_video_title_' . $v, [ 'label' => "Titulo video $v", 'section' => 't21_videos', 'type' => 'text' ] );
        $wp_customize->add_control( 't21_video_url_' . $v,   [ 'label' => "URL YouTube $v",  'section' => 't21_videos', 'type' => 'url' ] );
    }

    // Footer info
    $wp_customize->add_section( 't21_footer_info', [
        'title'    => __( 'Informacion del Footer', 'tiempo21-radiovictoria' ),
        'priority' => 60,
    ] );
    $wp_customize->add_setting( 't21_footer_copy', [ 'default' => '&copy; ' . date( 'Y' ) . ' Tiempo21 - Radio Victoria. Todos los derechos reservados.', 'sanitize_callback' => 'wp_kses_post' ] );
    $wp_customize->add_control( 't21_footer_copy', [ 'label' => 'Texto de copyright', 'section' => 't21_footer_info', 'type' => 'textarea' ] );

    // Performance & UX
    $wp_customize->add_section( 't21_ux', [
        'title'    => __( 'Rendimiento y UX', 'tiempo21-radiovictoria' ),
        'priority' => 65,
    ] );
    $wp_customize->add_setting( 't21_smooth_scroll', [
        'default'           => true,
        'sanitize_callback' => 't21_sanitize_checkbox',
    ] );
    $wp_customize->add_control( 't21_smooth_scroll', [
        'label'   => __( 'Activar Scroll Suave', 'tiempo21-radiovictoria' ),
        'section' => 't21_ux',
        'type'    => 'checkbox',
    ] );
    $wp_customize->add_setting( 't21_back_to_top', [
        'default'           => true,
        'sanitize_callback' => 't21_sanitize_checkbox',
    ] );
    $wp_customize->add_control( 't21_back_to_top', [
        'label'   => __( 'Boton "Volver Arriba"', 'tiempo21-radiovictoria' ),
        'section' => 't21_ux',
        'type'    => 'checkbox',
    ] );

    // Login Screen
    $wp_customize->add_section( 't21_login', [
        'title'    => __( 'Pantalla de Login', 'tiempo21-radiovictoria' ),
        'priority' => 65,
    ] );
    $wp_customize->add_setting( 't21_login_logo', [
        'default'           => '',
        'sanitize_callback' => 'absint',
    ] );
    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 't21_login_logo', [
        'label'     => __( 'Logo del sitio (Login)', 'tiempo21-radiovictoria' ),
        'section'   => 't21_login',
        'mime_type' => 'image',
    ] ) );

    // Breadcrumb
    $wp_customize->add_section( 't21_breadcrumb', [
        'title'    => __( 'Breadcrumb (Migas de Pan)', 'tiempo21-radiovictoria' ),
        'priority' => 66,
    ] );
    $wp_customize->add_setting( 't21_breadcrumb_enable', [
        'default'           => true,
        'sanitize_callback' => 't21_sanitize_checkbox',
    ] );
    $wp_customize->add_control( 't21_breadcrumb_enable', [
        'label'   => __( 'Mostrar Breadcrumb', 'tiempo21-radiovictoria' ),
        'section' => 't21_breadcrumb',
        'type'    => 'checkbox',
    ] );
    $wp_customize->add_setting( 't21_breadcrumb_home', [
        'default'           => __( 'Inicio', 'tiempo21-radiovictoria' ),
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 't21_breadcrumb_home', [
        'label'   => __( 'Texto para Inicio', 'tiempo21-radiovictoria' ),
        'section' => 't21_breadcrumb',
        'type'    => 'text',
    ] );
    $wp_customize->add_setting( 't21_breadcrumb_separator', [
        'default'           => '»',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 't21_breadcrumb_separator', [
        'label'   => __( 'Separador', 'tiempo21-radiovictoria' ),
        'section' => 't21_breadcrumb',
        'type'    => 'text',
    ] );

    // Share Buttons
    $wp_customize->add_section( 't21_share', [
        'title'    => __( 'Botones Compartir', 'tiempo21-radiovictoria' ),
        'priority' => 67,
    ] );
    $wp_customize->add_setting( 't21_share_enable', [
        'default'           => true,
        'sanitize_callback' => 't21_sanitize_checkbox',
    ] );
    $wp_customize->add_control( 't21_share_enable', [
        'label'   => __( 'Mostrar botones de compartir', 'tiempo21-radiovictoria' ),
        'section' => 't21_share',
        'type'    => 'checkbox',
    ] );
    $wp_customize->add_setting( 't21_share_position', [
        'default'           => 'after_meta',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 't21_share_position', [
        'label'   => __( 'Location', 'tiempo21-radiovictoria' ),
        'section' => 't21_share',
        'type'    => 'select',
        'choices' => [
            'after_meta'    => __( 'After date/author (top)', 'tiempo21-radiovictoria' ),
            'before_tags'   => __( 'Before tags (bottom)', 'tiempo21-radiovictoria' ),
            'both'          => __( 'Both locations', 'tiempo21-radiovictoria' ),
        ],
    ] );
    $wp_customize->add_setting( 't21_share_style', [
        'default'           => 'circle',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 't21_share_style', [
        'label'   => __( 'Button style', 'tiempo21-radiovictoria' ),
        'section' => 't21_share',
        'type'    => 'select',
        'choices' => [
            'circle'  => __( 'Circle', 'tiempo21-radiovictoria' ),
            'square'  => __( 'Square', 'tiempo21-radiovictoria' ),
            'simple'  => __( 'Simple (icons)', 'tiempo21-radiovictoria' ),
        ],
    ] );

    // SEO
    $wp_customize->add_section( 't21_seo', [
        'title'    => __( 'SEO', 'tiempo21-radiovictoria' ),
        'priority' => 68,
    ] );
    $wp_customize->add_setting( 't21_seo_enable', [
        'default'           => true,
        'sanitize_callback' => 't21_sanitize_checkbox',
    ] );
    $wp_customize->add_control( 't21_seo_enable', [
        'label'   => __( 'Activar meta tags SEO', 'tiempo21-radiovictoria' ),
        'section' => 't21_seo',
        'type'    => 'checkbox',
    ] );
    $wp_customize->add_setting( 't21_seo_twitter', [
        'default'           => '@Tiempo21Cuba',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 't21_seo_twitter', [
        'label'   => __( 'Usuario de Twitter (sin @)', 'tiempo21-radiovictoria' ),
        'section' => 't21_seo',
        'type'    => 'text',
    ] );

    // Security
    $wp_customize->add_section( 't21_security', [
        'title'    => __( 'Seguridad', 'tiempo21-radiovictoria' ),
        'priority' => 69,
    ] );
    $wp_customize->add_setting( 't21_security_enable', [
        'default'           => true,
        'sanitize_callback' => 't21_sanitize_checkbox',
    ] );
    $wp_customize->add_control( 't21_security_enable', [
        'label'   => __( 'Activar medidas de seguridad', 'tiempo21-radiovictoria' ),
        'section' => 't21_security',
        'type'    => 'checkbox',
    ] );

    // Posts Listing Options
    $wp_customize->add_section( 't21_posts_listing', [
        'title'    => __( 'Listado de Posts', 'tiempo21-radiovictoria' ),
        'priority' => 71,
    ] );

    // Layout type
    $wp_customize->add_setting( 't21_posts_layout', [
        'default'           => 'grid',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 't21_posts_layout', [
        'label'   => __( 'Tipo de diseño', 'tiempo21-radiovictoria' ),
        'section' => 't21_posts_listing',
        'type'    => 'radio',
        'choices' => [
            'grid' => __( 'Grid (2 columnas)', 'tiempo21-radiovictoria' ),
            'list' => __( 'List (imagen izquierda)', 'tiempo21-radiovictoria' ),
        ],
    ] );

    // Show/hide options
    $options = [
        't21_posts_show_image' => __( 'Mostrar imagen', 'tiempo21-radiovictoria' ),
        't21_posts_show_category' => __( 'Mostrar categoría', 'tiempo21-radiovictoria' ),
        't21_posts_show_title' => __( 'Mostrar título', 'tiempo21-radiovictoria' ),
        't21_posts_show_excerpt' => __( 'Mostrar excerpt', 'tiempo21-radiovictoria' ),
        't21_posts_show_date' => __( 'Mostrar fecha', 'tiempo21-radiovictoria' ),
        't21_posts_show_author' => __( 'Mostrar autor', 'tiempo21-radiovictoria' ),
    ];

    foreach ( $options as $key => $label ) {
        $wp_customize->add_setting( $key, [
            'default'           => true,
            'sanitize_callback' => 't21_sanitize_checkbox',
        ] );
        $wp_customize->add_control( $key, [
            'label'   => $label,
            'section' => 't21_posts_listing',
            'type'    => 'checkbox',
        ] );
    }

    // Excerpt length
    $wp_customize->add_setting( 't21_posts_excerpt_length', [
        'default'           => 15,
        'sanitize_callback' => 'absint',
    ] );
    $wp_customize->add_control( 't21_posts_excerpt_length', [
        'label'       => __( 'Longitud del excerpt (palabras)', 'tiempo21-radiovictoria' ),
        'section'     => 't21_posts_listing',
        'type'        => 'number',
    ] );
}
add_action( 'customize_register', 't21_customize_register' );

// Regenerar sitemaps al regenerar permalinks
function t21_regenerate_sitemaps_on_permalink_save() {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    $sitemap_gen = sitemap_generator_init();
    $sitemap_gen->regenerate_all_sitemaps();
}
add_action( 'flush_rewrite_rules_hard', 't21_regenerate_sitemaps_on_permalink_save', 10, 0 );

function t21_sanitize_checkbox( $checked ) {
    return ( bool ) $checked;
}

function t21_body_classes( $classes ) {
    if ( get_theme_mod( 't21_smooth_scroll', true ) ) {
        $classes[] = 'smooth-scroll';
    }
    return $classes;
}
add_filter( 'body_class', 't21_body_classes' );

/* =========================================
   VIEW COUNT SYSTEM
   ========================================= */

function t21_theme_activate() {
    t21_create_tables();
    
    // Inicializar sitemap y crear directorio
    $sitemap_gen = sitemap_generator_init();
    $sitemap_gen->activate();
}
add_action( 'after_switch_theme', 't21_theme_activate' );

// Generar sitemaps en primer inicio si no existen
function t21_init_sitemaps() {
    if ( get_option( 't21_sitemaps_generated' ) ) {
        return;
    }
    
    $sitemap_gen = sitemap_generator_init();
    $sitemap_gen->regenerate_all_sitemaps();
    
    update_option( 't21_sitemaps_generated', true );
}
add_action( 'init', 't21_init_sitemaps', 20 );

// CHANGED: Función de desactivación adaptada para temas
function t21_theme_deactivate( $new_name, $new_theme, $old_theme ) {
    // Solo limpia si el tema que se desactiva es el nuestro
    if ( $old_theme && $old_theme->get_stylesheet() === get_stylesheet() ) {
        wp_clear_scheduled_hook( 't21_daily_cleanup' );
        wp_clear_scheduled_hook( 't21_sitemap_scheduled_update' );
        
        $sitemap_gen = sitemap_generator_init();
        $sitemap_gen->deactivate();
        
        flush_rewrite_rules();
    }
}
// CHANGED: Reemplazado register_deactivation_hook por switch_theme (cambio de tema)
add_action( 'switch_theme', 't21_theme_deactivate', 10, 3 );

function t21_count_visit() {
    if ( ! is_single() || ! is_singular( 'post' ) ) {
        return;
    }
    
    $post_id = absint( get_the_ID() );
    if ( ! $post_id ) {
        return;
    }
    
    $cookie_name = 't21_visit_' . $post_id;
    if ( isset( $_COOKIE[ $cookie_name ] ) ) {
        return;
    }
    
    t21_increment_view_count( $post_id );
    
    setcookie( $cookie_name, '1', [
        'expires'  => time() + 3600,
        'path'     => '/',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Strict',
    ] );
}
add_action( 'wp', 't21_count_visit' );

// CHANGED: (Opcional) Fallback para crear tablas si por alguna razón no se crearon en la activación
function t21_check_tables() {
    if ( get_option( 't21_view_db_version' ) !== '2.0' ) {
        t21_create_tables();
    }
}
add_action( 'init', 't21_check_tables' );

/* =========================================
   HELPER FUNCTIONS
   ========================================= */

function t21_youtube_embed( $url ) {
    if ( empty( $url ) ) return '';
    $video_id = '';
    if ( preg_match( '/youtu\.be\/([a-zA-Z0-9_\-]+)/', $url, $m ) ) {
        $video_id = $m[1];
    } elseif ( preg_match( '/youtube\.com\/watch\?v=([a-zA-Z0-9_\-]+)/', $url, $m ) ) {
        $video_id = $m[1];
    } elseif ( preg_match( '/youtube\.com\/embed\/([a-zA-Z0-9_\-]+)/', $url, $m ) ) {
        $video_id = $m[1];
    }
    if ( ! $video_id ) return '';
    return 'https://www.youtube.com/embed/' . esc_attr( $video_id );
}

function t21_get_youtube_title( $url ) {
    if ( empty( $url ) ) return '';
    $video_id = '';
    if ( preg_match( '/youtu\.be\/([a-zA-Z0-9_\-]+)/', $url, $m ) ) {
        $video_id = $m[1];
    } elseif ( preg_match( '/youtube\.com\/watch\?v=([a-zA-Z0-9_\-]+)/', $url, $m ) ) {
        $video_id = $m[1];
    } elseif ( preg_match( '/youtube\.com\/embed\/([a-zA-Z0-9_\-]+)/', $url, $m ) ) {
        $video_id = $m[1];
    }
    if ( ! $video_id ) return '';

    $oembed_url = 'https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v=' . $video_id . '&format=json';
    $response = wp_remote_get( $oembed_url, [ 'timeout' => 5 ] );

    if ( ! is_wp_error( $response ) ) {
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        if ( isset( $data['title'] ) && ! empty( $data['title'] ) ) {
            return $data['title'];
        }
    }

    $noembed_url = 'https://noembed.com/embed?url=https://www.youtube.com/watch?v=' . $video_id;
    $response2 = wp_remote_get( $noembed_url, [ 'timeout' => 5 ] );

    if ( ! is_wp_error( $response2 ) ) {
        $body2 = wp_remote_retrieve_body( $response2 );
        $data2 = json_decode( $body2, true );
        if ( isset( $data2['title'] ) && ! empty( $data2['title'] ) ) {
            return $data2['title'];
        }
    }

    return '';
}

function t21_get_thumbnail( $post_id, $size = 'card-medium', $class = '' ) {
    $thumb_id = get_post_thumbnail_id( $post_id );
    if ( $thumb_id ) {
        $url = wp_get_attachment_url( $thumb_id );
        if ( $url ) {
            $path = str_replace( get_site_url(), ABSPATH, $url );
            if ( file_exists( $path ) ) {
                return get_the_post_thumbnail( $post_id, $size, [ 'class' => $class, 'loading' => 'lazy' ] );
            }
        }
    }
    $aspect = 'style="aspect-ratio:16/9"';
    return '<div class="img-placeholder ' . esc_attr( $class ) . '" ' . $aspect . '><i class="fa-solid fa-image"></i></div>';
}

// Auto-add alt text to post thumbnails
add_filter( 'wp_get_attachment_image_attributes', 't21_auto_alt_thumbnail', 10, 3 );

function t21_auto_alt_thumbnail( $attr, $attachment, $size ) {
    if ( empty( $attr['alt'] ) ) {
        $post = get_post( $attachment->post_parent );
        if ( $post && $post->post_title ) {
            $attr['alt'] = $post->post_title;
        }
    }
    return $attr;
}

function t21_get_social_icons( $class = '' ) {
    $networks = [
        'facebook'  => [ 'fa-brands fa-facebook-f',  get_theme_mod( 't21_social_facebook' ) ],
        'twitter'   => [ 'fa-brands fa-x-twitter',   get_theme_mod( 't21_social_twitter' ) ],
        'instagram' => [ 'fa-brands fa-instagram',   get_theme_mod( 't21_social_instagram' ) ],
        'youtube'   => [ 'fa-brands fa-youtube',     get_theme_mod( 't21_social_youtube' ) ],
        'telegram'  => [ 'fa-brands fa-telegram',    get_theme_mod( 't21_social_telegram' ) ],
    ];
    $out = '<ul class="social-links ' . esc_attr( $class ) . '">';
    foreach ( $networks as $key => $data ) {
        if ( ! empty( $data[1] ) ) {
            $out .= '<li><a href="' . esc_url( $data[1] ) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr( ucfirst( $key ) ) . '"><i class="' . esc_attr( $data[0] ) . '"></i></a></li>';
        }
    }
    $out .= '</ul>';
    return $out;
}

// Render post card for listings - uses template part
function t21_render_post_card() {
    get_template_part( 'template-parts/post-card' );
}

function t21_get_archive_title( $title ) {
    if ( is_category() ) {
        $title = single_cat_title( '', false );
    } elseif ( is_tag() ) {
        $title = single_tag_title( '', false );
    } elseif ( is_author() ) {
        $title = '<span class="vcard">' . get_the_author() . '</span>';
    } elseif ( is_post_type_archive() ) {
        $title = post_type_archive_title( '', false );
    } elseif ( is_tax() ) {
        $title = single_term_title( '', false );
    }
    return $title;
}
add_filter( 'get_the_archive_title', 't21_get_archive_title' );

add_filter( 'excerpt_length', function() { return 20; } );
add_filter( 'excerpt_more',   function() { return '&hellip;'; } );

/* =========================================
   FRONT-PAGE HELPER FUNCTIONS
   ========================================= */

function t21_get_category_posts( $n ) {
    $cat_slug = get_theme_mod( 't21_cat_slug_' . $n );
    $count    = (int) get_theme_mod( 't21_cat_count_' . $n, 5 );
    if ( ! $cat_slug ) return null;
    return new WP_Query( [ 
        'category_name'       => $cat_slug, 
        'posts_per_page'      => $count, 
        'ignore_sticky_posts' => true,
        'no_found_rows'      => true,
    ] );
}

function t21_get_category_title( $n ) {
    $cat_slug = get_theme_mod( 't21_cat_slug_' . $n );
    if ( ! $cat_slug ) return 'Category ' . $n;
    $cat = get_category_by_slug( $cat_slug );
    return $cat ? $cat->name : 'Category ' . $n;
}

add_action( 'pre_get_posts', 't21_exclude_sticky_from_home' );

function t21_exclude_sticky_from_home( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( $query->is_home() ) {
        $query->set( 'ignore_sticky_posts', true );
    }
}

/* =========================================
   INCLUDE ADDITIONAL FEATURES
    ========================================= */

require_once T21_DIR . '/inc/breadcrumb/class-breadcrumb.php';
require_once T21_DIR . '/inc/share-buttons.php';
require_once T21_DIR . '/inc/seo.php';
require_once T21_DIR . '/inc/security.php';
require_once T21_DIR . '/inc/login-customizer.php';

add_action( 'wp', 't21_maybe_disable_seo_security' );

function t21_maybe_disable_seo_security() {
    if ( ! get_theme_mod( 't21_seo_enable', true ) ) {
        remove_action( 'wp_head', 'tiempo21_seo_tags', 1 );
        remove_action( 'wp_head', 'tiempo21_agregar_hreflang', 2 );
        remove_filter( 'document_title_parts', 'tiempo21_mejorar_titulo_documento' );
    }
    if ( ! get_theme_mod( 't21_security_enable', true ) ) {
        remove_all_actions( 'wp_head', 20 );
    }
}

/* =========================================
   BREADCRUMB DISPLAY FUNCTION
   ========================================= */
function t21_display_breadcrumb() {
    if ( ! get_theme_mod( 't21_breadcrumb_enable', true ) ) {
        return;
    }
    
    $breadcrumb = new Breadcrumb_Shortcode();
    $home_text = get_theme_mod( 't21_breadcrumb_home', __( 'Inicio', 'tiempo21-radiovictoria' ) );
    $separator = get_theme_mod( 't21_breadcrumb_separator', '»' );
    
    echo do_shortcode( '[breadcrumb home_text="' . esc_attr( $home_text ) . '" separator="' . esc_attr( $separator ) . '"]' );
}

/* =========================================
   SHARE BUTTONS DISPLAY FUNCTION
   ========================================= */
function t21_display_share_buttons( $position = 'after_meta' ) {
    if ( ! get_theme_mod( 't21_share_enable', true ) ) {
        return;
    }
    
    $share_position = get_theme_mod( 't21_share_position', 'after_meta' );
    $share_style = get_theme_mod( 't21_share_style', 'circle' );
    
    if ( $share_position === $position || $share_position === 'both' ) {
        echo do_shortcode( '[t21_share_buttons style="' . esc_attr( $share_style ) . '" title="" align="left"]' );
    }
}
