<?php
/**
 * SEO Optimization dinamico para WordPress Tiempo21
 * 1- Genera todas las meta tags sin guardar nada en la base de datos
 * 2- Corrige errores detectados por PageSpeed
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Agrega todas las meta tags SEO en el <head>
 */
function tiempo21_seo_tags() {
    global $post;

    // ============================================
    // CONFIGURACION GENERAL DEL SITIO
    // ============================================
    $nombre_sitio    = get_bloginfo( 'name' );
    $descripcion_sitio = get_bloginfo( 'description' );
    $url_sitio       = home_url( '/' );
    $locale          = get_locale();

    // Configuracion de Twitter
    $twitter_usuario = get_theme_mod( 't21_seo_twitter', '@Tiempo21Cuba' );

    // ============================================
    // PAGINAS INDIVIDUALES (POSTS, PAGINAS)
    // ============================================
    if ( is_singular() ) {

        // DATOS BASICOS DEL POST
        $titulo              = get_the_title();
        $url                 = get_permalink();
        $autor               = get_the_author_meta( 'display_name', $post->post_author );
        $autor_url           = get_author_posts_url( $post->post_author );
        $fecha_publicacion   = get_the_date( 'c' );
        $fecha_modificacion  = get_the_modified_date( 'c' );

        // OBTENER DESCRIPCION DINAMICA
        $descripcion = tiempo21_get_description_dinamica();

        // OBTENER IMAGEN DESTACADA
        $imagen_destacada = tiempo21_get_imagen_destacada();

        // OBTENER KEYWORDS DINAMICAS
        $keywords = tiempo21_get_keywords_dinamicas();

        // TIPO DE CONTENIDO
        $tipo_contenido = is_single() ? 'article' : 'website';

        // Funciones de escape
        $descripcion_escaped = htmlspecialchars( $descripcion, ENT_QUOTES | ENT_HTML5, 'UTF-8', false );
        $titulo_escaped      = htmlspecialchars( $titulo . ' | ' . $nombre_sitio, ENT_QUOTES | ENT_HTML5, 'UTF-8', false );
        $keywords_escaped    = $keywords ? htmlspecialchars( $keywords, ENT_QUOTES | ENT_HTML5, 'UTF-8', false ) : '';

        // 1. META DESCRIPTION
        echo '<meta name="description" content="' . $descripcion_escaped . '">' . "\n";

        // 2. KEYWORDS
        if ( $keywords ) {
            echo '<meta name="keywords" content="' . $keywords_escaped . '">' . "\n";
        }

        // 3. OPEN GRAPH
        echo '<meta property="og:locale" content="' . esc_attr( $locale ) . '">' . "\n";
        echo '<meta property="og:type" content="' . esc_attr( $tipo_contenido ) . '">' . "\n";
        echo '<meta property="og:title" content="' . $titulo_escaped . '">' . "\n";
        echo '<meta property="og:description" content="' . $descripcion_escaped . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr( $nombre_sitio ) . '">' . "\n";

        if ( $imagen_destacada ) {
            echo '<meta property="og:image" content="' . esc_url( $imagen_destacada ) . '">' . "\n";
            echo '<meta property="og:image:secure_url" content="' . esc_url( $imagen_destacada ) . '">' . "\n";
            echo '<meta property="og:image:alt" content="' . esc_attr( $titulo ) . '">' . "\n";
        }

        // ARTICLE METADATA
        if ( 'article' === $tipo_contenido ) {
            echo '<meta property="article:publisher" content="' . esc_url( $url_sitio ) . '">' . "\n";
            echo '<meta property="article:published_time" content="' . esc_attr( $fecha_publicacion ) . '">' . "\n";
            echo '<meta property="article:modified_time" content="' . esc_attr( $fecha_modificacion ) . '">' . "\n";
            echo '<meta property="article:author" content="' . esc_attr( $autor ) . '">' . "\n";

            // Categorias
            $categorias = get_the_category();
            if ( $categorias ) {
                foreach ( $categorias as $categoria ) {
                    echo '<meta property="article:section" content="' . esc_attr( $categoria->name ) . '">' . "\n";
                }
            }

            // Etiquetas
            $etiquetas = get_the_tags();
            if ( $etiquetas ) {
                foreach ( $etiquetas as $etiqueta ) {
                    echo '<meta property="article:tag" content="' . esc_attr( $etiqueta->name ) . '">' . "\n";
                }
            }
        }

        // 4. TWITTER CARDS
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr( $titulo . ' | ' . $nombre_sitio ) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr( $descripcion ) . '">' . "\n";
        echo '<meta name="twitter:site" content="' . esc_attr( $twitter_usuario ) . '">' . "\n";
        echo '<meta name="twitter:creator" content="' . esc_attr( $twitter_usuario ) . '">' . "\n";

        if ( $imagen_destacada ) {
            echo '<meta name="twitter:image" content="' . esc_url( $imagen_destacada ) . '">' . "\n";
            echo '<meta name="twitter:image:alt" content="' . esc_attr( $titulo ) . '">' . "\n";
        }

        // 5. SCHEMA.ORG JSON-LD
        tiempo21_generar_json_ld();

        // 6. META TAGS ADICIONALES
        echo '<meta name="author" content="' . esc_attr( $autor ) . '">' . "\n";
        echo '<meta name="robots" content="index, follow">' . "\n";
        echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";

    }
    // ============================================
    // PAGINA PRINCIPAL
    // ============================================
    elseif ( is_home() || is_front_page() ) {

        $descripcion = $descripcion_sitio ?: 'Sitio web de noticias e informacion actualizada';

        // OPEN GRAPH
        echo '<meta property="og:title" content="' . esc_attr( $nombre_sitio ) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr( $descripcion ) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url( $url_sitio ) . '">' . "\n";
        echo '<meta property="og:type" content="website">' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr( $nombre_sitio ) . '">' . "\n";

        // TWITTER CARDS
        echo '<meta name="twitter:card" content="summary">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr( $nombre_sitio ) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr( $descripcion ) . '">' . "\n";
        echo '<meta name="twitter:site" content="' . esc_attr( $twitter_usuario ) . '">' . "\n";

        // JSON-LD PARA HOMEPAGE
        tiempo21_generar_json_ld_homepage();

        // META TAGS BASICOS
        echo '<meta name="description" content="' . esc_attr( $descripcion ) . '">' . "\n";
        echo '<link rel="canonical" href="' . esc_url( $url_sitio ) . '">' . "\n";

    }
    // ============================================
    // ARCHIVOS (CATEGORIAS, ETIQUETAS)
    // ============================================
    elseif ( is_category() || is_tag() || is_tax() ) {

        $term         = get_queried_object();
        $titulo       = single_term_title( '', false );
        $descripcion  = term_description( $term->term_id, $term->taxonomy );

        if ( empty( $descripcion ) ) {
            $descripcion = 'Archivo de ' . $titulo . ' - ' . $nombre_sitio;
        }

        $url                  = get_term_link( $term );
        $descripcion_limpia   = wp_strip_all_tags( $descripcion );
        $descripcion_limpia   = wp_trim_words( $descripcion_limpia, 30 );

        // OPEN GRAPH
        echo '<meta property="og:title" content="' . esc_attr( $titulo . ' | ' . $nombre_sitio ) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr( $descripcion_limpia ) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
        echo '<meta property="og:type" content="website">' . "\n";

        // TWITTER CARDS
        echo '<meta name="twitter:card" content="summary">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr( $titulo . ' | ' . $nombre_sitio ) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr( $descripcion_limpia ) . '">' . "\n";

        // META TAGS BASICOS
        echo '<meta name="description" content="' . esc_attr( $descripcion_limpia ) . '">' . "\n";
        echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";

    }
}
add_action( 'wp_head', 'tiempo21_seo_tags', 1 );

/**
 * Genera descripcion dinamica para SEO
 */
function tiempo21_get_description_dinamica() {
    global $post;

    // 1. Usar el excerpt si existe
    if ( has_excerpt() ) {
        $excerpt = get_the_excerpt();
        if ( ! empty( $excerpt ) ) {
            $excerpt = html_entity_decode( $excerpt, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
            return wp_trim_words( strip_tags( $excerpt ), 30 );
        }
    }

    // 2. Extraer del contenido
    $contenido        = get_the_content();
    $contenido_limpio = strip_tags( $contenido );
    $contenido_limpio = preg_replace( '/\s+/', ' ', $contenido_limpio );
    $contenido_limpio = html_entity_decode( $contenido_limpio, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

    if ( empty( $contenido_limpio ) ) {
        return get_the_title() . ' - ' . get_bloginfo( 'name' );
    }

    return wp_trim_words( $contenido_limpio, 30 );
}

/**
 * Genera keywords dinamicas
 */
function tiempo21_get_keywords_dinamicas() {
    global $post;

    $keywords = array();

    // 1. Agregar etiquetas del post
    $etiquetas = get_the_tags();
    if ( $etiquetas ) {
        foreach ( $etiquetas as $etiqueta ) {
            $keywords[] = $etiqueta->name;
        }
    }

    // 2. Agregar categorias
    $categorias = get_the_category();
    if ( $categorias ) {
        foreach ( $categorias as $categoria ) {
            $keywords[] = $categoria->name;
        }
    }

    // 3. Agregar palabras del titulo
    $titulo_palabras   = explode( ' ', get_the_title() );
    $palabras_comunes  = array( 'el', 'la', 'los', 'las', 'un', 'una', 'unos', 'unas', 'de', 'del', 'en', 'y', 'o', 'a', 'para', 'por', 'con', 'sin', 'sobre' );

    foreach ( $titulo_palabras as $palabra ) {
        $palabra_limpia = trim( strtolower( $palabra ), ' ,.;:!?' );
        if ( ! in_array( $palabra_limpia, $palabras_comunes, true ) && strlen( $palabra_limpia ) > 3 ) {
            $keywords[] = $palabra;
        }
    }

    // 4. Agregar nombre del sitio
    $keywords[] = get_bloginfo( 'name' );

    $keywords = array_unique( $keywords );
    $keywords = array_slice( $keywords, 0, 15 );

    return implode( ', ', $keywords );
}

/**
 * Obtiene imagen destacada o primera imagen del contenido
 */
function tiempo21_get_imagen_destacada() {
    global $post;

    // 1. Imagen destacada
    if ( has_post_thumbnail() ) {
        $imagen_url = get_the_post_thumbnail_url( $post->ID, 'full' );
        if ( $imagen_url ) {
            return $imagen_url;
        }
    }

    // 2. Buscar primera imagen en el contenido
    $contenido = get_the_content();
    $output    = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $contenido, $matches );

    if ( $output && isset( $matches[1][0] ) ) {
        return $matches[1][0];
    }

    // 3. Imagen por defecto
    $imagen_default = get_stylesheet_directory_uri() . '/images/default-seo-image.jpg';
    if ( file_exists( get_stylesheet_directory() . '/images/default-seo-image.jpg' ) ) {
        return $imagen_default;
    }

    return '';
}

/**
 * Genera JSON-LD para paginas individuales
 */
function tiempo21_generar_json_ld() {
    global $post;

    if ( ! is_singular() ) {
        return;
    }

    $titulo             = get_the_title();
    $descripcion        = tiempo21_get_description_dinamica();
    $url                = get_permalink();
    $autor              = get_the_author_meta( 'display_name', $post->post_author );
    $autor_url          = get_author_posts_url( $post->post_author );
    $fecha_publicacion  = get_the_date( 'c' );
    $fecha_modificacion = get_the_modified_date( 'c' );
    $imagen             = tiempo21_get_imagen_destacada();
    $nombre_sitio       = get_bloginfo( 'name' );
    $url_sitio          = home_url( '/' );

    // Obtener logo del sitio
    $logo_url = '';
    if ( has_custom_logo() ) {
        $custom_logo_id = get_theme_mod( 'custom_logo' );
        $logo_data      = wp_get_attachment_image_src( $custom_logo_id, 'full' );
        $logo_url       = $logo_data[0];
    } elseif ( file_exists( get_stylesheet_directory() . '/images/logo.png' ) ) {
        $logo_url = get_stylesheet_directory_uri() . '/images/logo.png';
    }

    $schema = array(
        '@context'         => 'https://schema.org',
        '@type'           => is_single() ? 'Article' : 'WebPage',
        'headline'        => $titulo,
        'description'     => $descripcion,
        'url'             => $url,
        'datePublished'   => $fecha_publicacion,
        'dateModified'    => $fecha_modificacion,
        'mainEntityOfPage' => array(
            '@type' => 'WebPage',
            '@id'   => $url
        ),
        'publisher'       => array(
            '@type' => 'Organization',
            'name'  => $nombre_sitio,
            'url'   => $url_sitio
        ),
        'author'          => array(
            '@type' => 'Person',
            'name'  => $autor,
            'url'   => $autor_url
        )
    );

    if ( $logo_url ) {
        $schema['publisher']['logo'] = array(
            '@type' => 'ImageObject',
            'url'   => $logo_url
        );
    }

    if ( $imagen ) {
        $schema['image'] = array(
            '@type' => 'ImageObject',
            'url'   => $imagen
        );
    }

    if ( is_single() ) {
        $categorias = get_the_category();
        if ( $categorias ) {
            $categorias_nombres = array();
            foreach ( $categorias as $categoria ) {
                $categorias_nombres[] = $categoria->name;
            }
            $schema['articleSection'] = implode( ', ', $categorias_nombres );
        }

        $etiquetas = get_the_tags();
        if ( $etiquetas ) {
            $keywords = array();
            foreach ( $etiquetas as $etiqueta ) {
                $keywords[] = $etiqueta->name;
            }
            $schema['keywords'] = implode( ', ', $keywords );
        }
    }

    echo '<script type="application/ld+json">' . json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}

/**
 * Genera JSON-LD para la pagina principal
 */
function tiempo21_generar_json_ld_homepage() {
    if ( ! is_home() && ! is_front_page() ) {
        return;
    }

    $nombre_sitio  = get_bloginfo( 'name' );
    $descripcion   = get_bloginfo( 'description' );
    $url_sitio     = home_url( '/' );

    $logo_url = '';
    if ( has_custom_logo() ) {
        $custom_logo_id = get_theme_mod( 'custom_logo' );
        $logo_data      = wp_get_attachment_image_src( $custom_logo_id, 'full' );
        $logo_url       = $logo_data[0];
    } elseif ( file_exists( get_stylesheet_directory() . '/images/logo.png' ) ) {
        $logo_url = get_stylesheet_directory_uri() . '/images/logo.png';
    }

    $schema = array(
        '@context'              => 'https://schema.org',
        '@type'                 => 'WebSite',
        'name'                  => $nombre_sitio,
        'description'           => $descripcion,
        'url'                   => $url_sitio,
        'potentialAction'       => array(
            array(
                '@type'          => 'SearchAction',
                'target'         => $url_sitio . '?s={search_term_string}',
                'query-input'    => 'required name=search_term_string'
            )
        )
    );

    $organization_schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => $nombre_sitio,
        'url'      => $url_sitio
    );

    if ( $logo_url ) {
        $organization_schema['logo'] = $logo_url;
        $organization_schema['image'] = $logo_url;
    }

    echo '<script type="application/ld+json">' . json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    echo '<script type="application/ld+json">' . json_encode( $organization_schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}

/**
 * Agrega hreflang para multidioma basico
 */
function tiempo21_agregar_hreflang() {
    if ( is_singular() ) {
        $url = get_permalink();
        echo '<link rel="alternate" hreflang="es" href="' . esc_url( $url ) . '">' . "\n";
        echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( $url ) . '">' . "\n";
    }
}
add_action( 'wp_head', 'tiempo21_agregar_hreflang', 2 );

/**
 * Mejora la etiqueta <title>
 */
function tiempo21_mejorar_titulo_documento( $title_parts ) {
    if ( is_admin() || is_feed() ) {
        return $title_parts;
    }

    $nombre_sitio = get_bloginfo( 'name' );

    if ( is_front_page() || is_home() ) {
        $title_parts['title']   = $nombre_sitio;
        $title_parts['tagline'] = get_bloginfo( 'description' );
    } elseif ( is_singular() ) {
        $title_parts['site'] = $nombre_sitio;
    } elseif ( is_category() || is_tag() ) {
        $title_parts['title'] = single_term_title( '', false );
        $title_parts['site']  = $nombre_sitio;
    } elseif ( is_archive() ) {
        $title_parts['title'] = get_the_archive_title();
        $title_parts['title'] = wp_strip_all_tags( $title_parts['title'] );
        $title_parts['site']  = $nombre_sitio;
    }

    return $title_parts;
}
add_filter( 'document_title_parts', 'tiempo21_mejorar_titulo_documento', 10, 1 );
