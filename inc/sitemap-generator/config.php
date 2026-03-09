<?php
/**
 * Configuración del generador de sitemaps
 * 
 * @package Sitemap_Generator
 */

if (!defined('ABSPATH')) {
    exit;
}

// Configuración por defecto
define('SITEMAP_MAX_URLS', 1000);
define('SITEMAP_CACHE_TIME', 3600); // 1 hora
define('SITEMAP_EXCLUDE_POST_TYPES', array('attachment', 'revision', 'nav_menu_item'));
define('SITEMAP_INCLUDE_POST_TYPES', array('post', 'page'));

// Frecuencias de cambio posibles
$sitemap_changefreq_options = array(
    'always'  => 'Siempre',
    'hourly'  => 'Cada hora',
    'daily'   => 'Diario',
    'weekly'  => 'Semanal',
    'monthly' => 'Mensual',
    'yearly'  => 'Anual',
    'never'   => 'Nunca'
);

// Prioridades por defecto
$sitemap_priorities = array(
    'homepage'   => 1.0,
    'posts'      => 0.9,
    'pages'      => 0.7,
    'categories' => 0.6,
    'tags'       => 0.5,
    'archives'   => 0.4
);