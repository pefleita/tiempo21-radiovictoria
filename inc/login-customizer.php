<?php
/**
 * Custom Login Screen - Login Form Customization
 * 
 * @package tiempo21-radiovictoria
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shange logo on wp-login.php
 */
function t21_custom_login_logo() {
    $logo_id = get_theme_mod( 't21_login_logo' );
    if ( $logo_id ) {
        $logo_url = wp_get_attachment_url( $logo_id );
        if ( $logo_url ) {
            echo '<style type="text/css">
                #login h1 a, .login h1 a {
                    background-image: url(' . esc_url( $logo_url ) . ');
                    width: 320px;
                    background-size: contain;
                    background-repeat: no-repeat;
                }
            </style>';
        }
    }
}
add_action( 'login_enqueue_scripts', 't21_custom_login_logo' );

/**
 * Change URL logo (go to home, not to wordpress.org)
 */
function t21_custom_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 't21_custom_login_logo_url' );

/**
 * Change logo title
 */
function t21_custom_login_logo_url_title() {
    return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 't21_custom_login_logo_url_title' );
