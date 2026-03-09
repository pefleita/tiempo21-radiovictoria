<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php if ( ! get_theme_mod( 't21_smooth_scroll', true ) ) : ?>
<style>html { scroll-behavior: auto !important; }</style>
<?php endif; ?>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Saltar al contenido principal', 'tiempo21' ); ?></a>

<header class="site-header" id="site-header">

    <!-- Top bar: Fecha + Redes Sociales -->
    <div class="header-topbar">
        <div class="container">
            <div class="header-topbar__inner">
                <div class="header-topbar__date">
                    <span><?php echo date_i18n( 'l, j \d\e F \d\e Y' ); ?></span>
                </div>
                <div class="header-topbar__social">
                    <?php
                    $socials = [
                        't21_social_facebook'  => [ 'fa-brands fa-facebook-f',  'Facebook' ],
                        't21_social_twitter'   => [ 'fa-brands fa-x-twitter',   'Twitter' ],
                        't21_social_instagram' => [ 'fa-brands fa-instagram',   'Instagram' ],
                        't21_social_youtube'   => [ 'fa-brands fa-youtube',     'YouTube' ],
                        't21_social_telegram'  => [ 'fa-brands fa-telegram',    'Telegram' ],
                    ];
                    foreach ( $socials as $key => $data ) :
                        $url = get_theme_mod( $key );
                        if ( $url ) : ?>
                            <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $data[1] ); ?>">
                                <i class="<?php echo esc_attr( $data[0] ); ?>"></i>
                            </a>
                        <?php endif;
                    endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Banner / Logo -->
    <?php
    $banner_image_id = get_theme_mod( 't21_banner_image' );
    $hide_title = get_theme_mod( 't21_hide_site_title', false );
    $hide_description = get_theme_mod( 't21_hide_site_description', false );
    $show_text = ! $hide_title || ! $hide_description;
    $site_name = get_bloginfo( 'name' );
    $site_desc = get_bloginfo( 'description' );
    $banner_url = $banner_image_id ? wp_get_attachment_url( $banner_image_id ) : '';
    ?>
    <div class="header-banner">
        <div class="container">
            <?php if ( $show_text ) : ?>
                <?php if ( $site_name ) : ?>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="header-banner__title">
                        <?php echo esc_html( $site_name ); ?>
                    </a>
                <?php endif; ?>
                <?php if ( $site_desc ) : ?>
                    <span class="header-banner__description"><?php echo esc_html( $site_desc ); ?></span>
                <?php endif; ?>
            <?php elseif ( $banner_url ) : ?>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="header-banner__img" style="background-image: url('<?php echo esc_url( $banner_url ); ?>');">
                    <?php echo esc_html( $site_name ); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

</header>

<!-- Navbar: Audio + Menu + Buscador (Sticky) -->
<nav class="header-navbar" id="header-navbar">
    <div class="container">
        <div class="header-navbar__inner">

            <!-- Audio Player -->
            <?php $audio_url = get_theme_mod( 't21_audio_url' ); ?>
            <?php if ( $audio_url ) : ?>
            <div class="audio-player" id="audio-player">
                <button class="audio-player__btn" id="audio-toggle" aria-label="Reproducir/Pausar radio" title="<?php echo esc_attr( get_theme_mod( 't21_audio_label', 'Radio Victoria' ) ); ?>">
                    <i class="fa-solid fa-play" id="audio-icon"></i>
                </button>
                <div class="audio-player__info">
                    <strong><?php echo esc_html( get_theme_mod( 't21_audio_label', 'Radio Victoria 1170 AM' ) ); ?></strong>
                    <span id="audio-status">En vivo</span>
                </div>
                <audio id="radio-stream" preload="none">
                    <source src="<?php echo esc_url( $audio_url ); ?>">
                </audio>
            </div>
            <?php endif; ?>

            <!-- Primary Menu -->
            <div class="primary-nav" id="primary-nav">
                <?php wp_nav_menu( [
                    'theme_location' => 'primary',
                    'menu_class'     => '',
                    'container'      => false,
                    'fallback_cb'    => function() {
                        echo '<ul><li><a href="' . esc_url( home_url( '/' ) ) . '">Inicio</a></li></ul>';
                    },
                ] ); ?>
            </div>

            <!-- Search Toggle -->
            <button class="search-toggle" id="search-toggle" aria-label="Abrir buscador">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>

            <!-- Mobile menu toggle -->
            <button class="nav-toggle" id="nav-toggle" aria-label="Abrir menú" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>

        </div>
    </div>
</nav>

<!-- Search Overlay -->
<div class="search-overlay" id="search-overlay" role="dialog" aria-modal="true" aria-label="Buscador">
    <div class="search-overlay__box">
        <button class="search-overlay__close" id="search-close" aria-label="Cerrar buscador">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <p class="search-overlay__title"><i class="fa-solid fa-magnifying-glass"></i> Buscar noticias</p>
        <?php get_search_form(); ?>
    </div>
</div>

<div id="page" class="site">
    <main id="main" class="site-main">
        <div class="container">
