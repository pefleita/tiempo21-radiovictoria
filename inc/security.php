<?php
/**
 * =============================================
 * WORDPRESS SECURITY: COMPLETE FINGERPRINT REMOVAL
 * Optimized for Tiempo21 Theme
 * =============================================
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. REMOVE WORDPRESS GENERATOR (HEAD, RSS, REST API)
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
add_filter('the_generator', '__return_empty_string');
add_filter('wp_headers', function($headers) {
    unset($headers['X-Powered-By']);
    unset($headers['Server']);
    return $headers;
});

// 2. REMOVE ?ver= PARAMETERS FROM SCRIPTS AND STYLES
add_filter('style_loader_src', 't21_remove_script_version', 9999);
add_filter('script_loader_src', 't21_remove_script_version', 9999);
function t21_remove_script_version($src) {
    if ($src && strpos($src, '?ver=') !== false) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}

// 3. REMOVE META GENERATOR - improved version without output buffering
add_filter('wp_robots', function($robots) {
    $robots['generator'] = false;
    return $robots;
});

// 4. REMOVE VERSIONS FROM RSS FEEDS
add_filter('the_generator', '__return_empty_string');

// 5. REMOVE VERSIONS FROM PLUGIN URLS
add_filter('wp_resource_hints', function($hints, $relation_type) {
    if ('dns-prefetch' === $relation_type) {
        foreach ($hints as $key => $url) {
            $hints[$key] = remove_query_arg('ver', $url);
        }
    }
    return $hints;
}, 10, 2);

// 6. REMOVE WORDPRESS VERSION FROM FOOTER
add_filter('admin_footer_text', '__return_empty_string');
add_filter('update_footer', '__return_empty_string', 11);

// 7. PREVENT USER ENUMERATION
add_action('init', 't21_prevent_user_enumeration');
function t21_prevent_user_enumeration() {
    if ( ! is_admin() && isset( $_SERVER['REQUEST_URI'] ) ) {
        if ( preg_match( '/(wp-comments-post)/', sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) === 0 && ! empty( $_REQUEST['author'] ) ) {
            wp_die( esc_html__( 'Access denied', 'tiempo21' ), 'Forbidden', [ 'response' => 403 ] );
        }
    }
}

// 8. CUSTOMIZE LOGIN ERROR MESSAGES
add_filter('wp_login_errors', 't21_customize_login_errors', 10, 2);
function t21_customize_login_errors($errors, $redirect_to) {
    $codes = array('invalid_username', 'invalid_email', 'incorrect_password');
    foreach ($codes as $code) {
        if ($errors->get_error_message($code)) {
            $errors->remove($code);
            $errors->add('invalid_credentials', '<strong>ERROR</strong>: ' . esc_html__( 'Invalid credentials.', 'tiempo21' ) . ' <a href="' . esc_url( wp_lostpassword_url() ) . '">' . esc_html__( 'Forgot your password?', 'tiempo21' ) . '</a>');
            break;
        }
    }
    return $errors;
}

// 9. DISABLE SENSITIVE REST API ENDPOINTS
add_action('after_setup_theme', 't21_remove_json_api');
function t21_remove_json_api() {
    remove_action('wp_head', 'rest_output_link_wp_head', 10);
    remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
    remove_action('rest_api_init', 'wp_oembed_register_route');
    add_filter('embed_oembed_discover', '__return_false');
    remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');
}

// Disable user routes in REST API
add_filter('rest_endpoints', function($endpoints) {
    unset($endpoints['/wp/v2/users']);
    unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    return $endpoints;
});

// 10. NONCE FOR COMMENTS
add_action('comment_form', 't21_add_comment_nonce');
function t21_add_comment_nonce() {
    wp_nonce_field('t21_comment_nonce', 't21_csrf_comment');
}

add_action('pre_comment_on_post', 't21_verify_comment_nonce');
function t21_verify_comment_nonce() {
    if ( ! isset( $_POST['t21_csrf_comment'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['t21_csrf_comment'] ) ), 't21_comment_nonce' ) ) {
        wp_die(
            esc_html__( 'Security error. Try again.', 'tiempo21' ),
            'Nonce verification failed',
            [ 'response' => 403 ]
        );
    }
}

// Nonce for search
add_filter('get_search_form', 't21_add_search_nonce');
function t21_add_search_nonce($form) {
    $nonce = wp_nonce_field('t21_search_nonce', 't21_csrf_search', true, false);
    return str_replace('</form>', $nonce . '</form>', $form);
}

// 11. REMOVE HTTP HEADERS HINTS
add_filter('wp_headers', function($headers) {
    unset($headers['X-Powered-By']);
    unset($headers['Server']);
    unset($headers['X-Pingback']);
    unset($headers['Link']);
    return $headers;
});

// Disable XML-RPC
add_filter('xmlrpc_enabled', '__return_false');

// 12. REMOVE WORDPRESS LOGO FROM ADMIN BAR
add_action('admin_bar_menu', 't21_remove_wp_admin_bar_logo', 999);
function t21_remove_wp_admin_bar_logo($wp_admin_bar) {
    $wp_admin_bar->remove_node('wp-logo');
}

// CHANGE LOGIN TITLE
add_filter('login_headertext', '__return_empty_string');
add_filter('login_headerurl', function() {
    return home_url();
});

// REMOVE ADMIN FOOTER TEXT
add_filter('admin_footer_text', '__return_empty_string');
add_filter('update_footer', '__return_empty_string', 11);

// 13. REMOVE WORDPRESS TEST COOKIE
add_action('init', 't21_remove_cookie_test');
function t21_remove_cookie_test() {
    if (isset($_COOKIE['wordpress_test_cookie'])) {
        setcookie('wordpress_test_cookie', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
    }
}

// 14. IMPROVE ADMIN TITLE
add_filter('admin_title', function($admin_title) {
    return str_replace('WordPress', 'Admin', $admin_title);
});
add_filter('login_title', function($login_title) {
    return str_replace('WordPress', 'Login', $login_title);
});

// END OF SECURITY CODE
