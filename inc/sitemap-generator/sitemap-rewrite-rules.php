<?php
/**
 * Manejo de rewrite rules para sitemaps
 * 
 * @package Sitemap_Generator
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Filtrar query vars para sitemaps
 */
function sitemap_query_vars($vars) {
    $vars[] = 'sitemap';
    $vars[] = 'sitemap_page';
    return $vars;
}
add_filter('query_vars', 'sitemap_query_vars');

/**
 * Prevenir canonical redirect en sitemaps
 */
function sitemap_prevent_canonical_redirect($redirect_url, $requested_url) {
    if (preg_match('/sitemap(-\w+(-\d+)?)?\.xml$/', $requested_url)) {
        return false;
    }
    return $redirect_url;
}
add_filter('redirect_canonical', 'sitemap_prevent_canonical_redirect', 10, 2);