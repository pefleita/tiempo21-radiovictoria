<?php
/**
 * View Counter System for Tiempo21
 * Simplified version - no IP tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================================
    TABLE CREATION
    ========================================= */

function t21_create_tables() {
    global $wpdb;
    
    $views_table = $wpdb->prefix . 'post_views';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql_visitas = "CREATE TABLE IF NOT EXISTS $views_table (
        post_id bigint(20) UNSIGNED NOT NULL,
        total_views bigint(20) UNSIGNED DEFAULT 0,
        views_today int(11) DEFAULT 0,
        last_view datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (post_id),
        KEY idx_total_views (total_views),
        KEY idx_views_today (views_today),
        KEY idx_last_view (last_view)
    ) $charset_collate;";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql_visitas );
    
    t21_cleanup_old_detail_table();
    
    update_option( 't21_view_system_installed', '2.0' );
    update_option( 't21_view_db_version', '2.0' );
    
    if ( ! wp_next_scheduled( 't21_daily_cleanup' ) ) {
        wp_schedule_event( time(), 'daily', 't21_daily_cleanup' );
    }
}

function t21_tables_exist() {
    global $wpdb;
    $views_table = $wpdb->prefix . 'post_views';
    return $wpdb->get_var( "SHOW TABLES LIKE '$views_table'" ) === $views_table;
}

function t21_cleanup_old_detail_table() {
    global $wpdb;
    $detail_table = $wpdb->prefix . 'post_views_detail';
    
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$detail_table'" ) === $detail_table ) {
        $wpdb->query( "DROP TABLE IF EXISTS $detail_table" );
    }
}

/* =========================================
   VIEW COUNTING
   ========================================= */

function t21_increment_view_count( $post_id ) {
    if ( ! t21_tables_exist() ) {
        return false;
    }
    
    global $wpdb;
    
    $post_id = absint( $post_id );
    if ( ! $post_id ) {
        return false;
    }
    
    $views_table = $wpdb->prefix . 'post_views';
    
    $wpdb->query( $wpdb->prepare(
        "INSERT INTO $views_table (post_id, total_views, views_today, last_view) 
         VALUES (%d, 1, 1, NOW()) 
         ON DUPLICATE KEY UPDATE 
         total_views = total_views + 1,
         views_today = IF(DATE(last_view) = CURDATE(), views_today + 1, 1),
         last_view = NOW()",
        $post_id
    ) );
    
    clean_post_cache( $post_id );
    
    t21_clear_transients();
    
    return true;
}

function t21_clear_transients() {
    global $wpdb;
    $wpdb->query(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_t21_popular_%'"
    );
    $wpdb->query(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_t21_popular_%'"
    );
}

/* =========================================
   QUERIES
   ========================================= */

function t21_get_total_views( $post_id = null ) {
    if ( ! t21_tables_exist() ) {
        return 0;
    }
    
    global $wpdb;
    
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    
    if ( ! $post_id ) {
        return 0;
    }
    
    $views_table = $wpdb->prefix . 'post_views';
    
    static $cache = [];
    if ( isset( $cache[ $post_id ] ) ) {
        return $cache[ $post_id ];
    }
    
    $views = $wpdb->get_var( $wpdb->prepare(
        "SELECT total_views FROM $views_table WHERE post_id = %d",
        $post_id
    ) );
    
    $result = $views ? intval( $views ) : 0;
    $cache[ $post_id ] = $result;
    
    return $result;
}

function t21_get_popular_posts( $days = 30, $limit = 10, $category_slug = '' ) {
    if ( ! t21_tables_exist() ) {
        return [];
    }
    
    global $wpdb;
    
    $days = max( 1, intval( $days ) );
    $limit = max( 1, min( 50, intval( $limit ) ) );
    $category_slug = sanitize_text_field( $category_slug );
    
    $transient_key = 't21_popular_' . md5( "{$days}_{$limit}_{$category_slug}" );
    $cached = get_transient( $transient_key );
    if ( false !== $cached ) {
        return $cached;
    }
    
    $views_table = $wpdb->prefix . 'post_views';
    
    $query = "SELECT v.post_id, v.total_views as views
              FROM {$views_table} v
              INNER JOIN {$wpdb->posts} p ON v.post_id = p.ID
              WHERE p.post_status = 'publish'
              AND p.post_type = 'post'";
    
    if ( ! empty( $category_slug ) && $category_slug !== 'all' ) {
        $category = get_category_by_slug( $category_slug );
        if ( $category ) {
            $query .= $wpdb->prepare(
                " AND p.ID IN (
                    SELECT tr.object_id 
                    FROM {$wpdb->term_relationships} tr
                    INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                    WHERE tt.taxonomy = 'category' AND t.slug = %s
                )",
                $category_slug
            );
        }
    }
    
    $query .= " GROUP BY v.post_id, v.total_views ORDER BY v.total_views DESC LIMIT %d";
    $query = $wpdb->prepare( $query, $limit );
    $results = $wpdb->get_results( $query );
    
    if ( empty( $results ) ) {
        $posts = t21_get_popular_posts_fallback( $limit, $category_slug );
        set_transient( $transient_key, $posts, 300 );
        return $posts;
    }
    
    $posts = [];
    foreach ( $results as $item ) {
        $post_id = intval( $item->post_id );
        $post = get_post( $post_id );
        
        if ( $post && $post->post_status === 'publish' ) {
            $posts[] = [
                'ID'      => $post_id,
                'title'   => get_the_title( $post_id ),
                'link'    => get_permalink( $post_id ),
                'views'   => intval( $item->views ),
                'date'    => get_the_date( 'd/m/Y', $post_id ),
                'excerpt' => wp_trim_words( get_the_excerpt( $post_id ), 15, '...' )
            ];
        }
    }
    
    set_transient( $transient_key, $posts, 300 );
    return $posts;
}

function t21_get_popular_posts_fallback( $limit = 10, $category_slug = '' ) {
    if ( ! t21_tables_exist() ) {
        return [];
    }
    
    global $wpdb;
    
    $views_table = $wpdb->prefix . 'post_views';
    
    $query = "SELECT v.post_id, v.total_views as views
              FROM {$views_table} v
              INNER JOIN {$wpdb->posts} p ON v.post_id = p.ID
              WHERE p.post_status = 'publish' AND p.post_type = 'post'";
    
    if ( ! empty( $category_slug ) && $category_slug !== 'all' ) {
        $category = get_category_by_slug( $category_slug );
        if ( $category ) {
            $query .= $wpdb->prepare(
                " AND p.ID IN (
                    SELECT tr.object_id 
                    FROM {$wpdb->term_relationships} tr
                    INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                    WHERE tt.taxonomy = 'category' AND t.slug = %s
                )",
                $category_slug
            );
        }
    }
    
    $query .= " ORDER BY v.total_views DESC LIMIT %d";
    $query = $wpdb->prepare( $query, $limit );
    $results = $wpdb->get_results( $query );
    
    $posts = [];
    foreach ( $results as $item ) {
        $post_id = intval( $item->post_id );
        $post = get_post( $post_id );
        
        if ( $post && $post->post_status === 'publish' ) {
            $posts[] = [
                'ID'      => $post_id,
                'title'   => get_the_title( $post_id ),
                'link'    => get_permalink( $post_id ),
                'views'   => intval( $item->views ),
                'date'    => get_the_date( 'd/m/Y', $post_id ),
                'excerpt' => wp_trim_words( get_the_excerpt( $post_id ), 15, '...' )
            ];
        }
    }
    
    return $posts;
}

/* =========================================
   SHORTCODES
   ========================================= */

function t21_shortcode_view_count( $atts ) {
    $atts = shortcode_atts( [
        'id' => get_the_ID()
    ], $atts );
    
    $post_id = intval( $atts['id'] );
    
    if ( ! $post_id ) {
        return '0';
    }
    
    $views = t21_get_total_views( $post_id );
    
    return '<span class="view-count">' . number_format_i18n( $views ) . '</span>';
}
add_shortcode( 'visitas', 't21_shortcode_view_count' );

function t21_shortcode_popular_posts( $atts ) {
    $atts = shortcode_atts( [
        'limit'           => 10,
        'days'            => 30,
        'category'        => '',
        'show_views'      => 'yes',
        'show_date'       => 'no',
        'show_excerpt'    => 'no',
        'title'           => ''
    ], $atts );
    
    $limit = intval( $atts['limit'] );
    $days = intval( $atts['days'] );
    $category = sanitize_text_field( $atts['category'] );
    $show_views = ( $atts['show_views'] === 'yes' );
    $show_date = ( $atts['show_date'] === 'yes' );
    $show_excerpt = ( $atts['show_excerpt'] === 'yes' );
    $title = sanitize_text_field( $atts['title'] );
    
    $limit = max( 1, min( 50, $limit ) );
    $days = max( 1, min( 365, $days ) );
    
    $posts = t21_get_popular_posts( $days, $limit, $category );
    
    if ( empty( $posts ) ) {
        return '<div class="popular-posts empty"><p>No posts with views in the specified period.</p></div>';
    }
    
    $output = '<div class="popular-posts">';
    
    if ( ! empty( $title ) ) {
        $output .= '<h2 class="popular-posts-title">' . esc_html( $title ) . '</h2>';
    }
    
    $output .= '<ul class="popular-posts-list">';
    
    foreach ( $posts as $index => $post ) {
        $output .= '<li class="popular-post-item">';
        $output .= '<a href="' . esc_url( $post['link'] ) . '" class="popular-post-title">' . esc_html( $post['title'] ) . '</a>';
        
        $meta_items = [];
        
        if ( $show_views ) {
            $meta_items[] = '<span class="view-count">' . number_format_i18n( $post['views'] ) . ' views</span>';
        }
        
        if ( $show_date ) {
            $meta_items[] = '<span class="post-date">' . esc_html( $post['date'] ) . '</span>';
        }
        
        if ( ! empty( $meta_items ) ) {
            $output .= '<div class="post-meta">' . implode( ' | ', $meta_items ) . '</div>';
        }
        
        if ( $show_excerpt && ! empty( $post['excerpt'] ) ) {
            $output .= '<div class="post-excerpt">' . esc_html( $post['excerpt'] ) . '</div>';
        }
        
        $output .= '</li>';
    }
    
    $output .= '</ul>';
    $output .= '</div>';
    
    return $output;
}
add_shortcode( 'posts_mas_vistos', 't21_shortcode_popular_posts' );

/* =========================================
   ADMIN - VIEW COLUMN
   ========================================= */

function t21_add_admin_column( $columns ) {
    $columns['views'] = '<span title="Views">👁️</span>';
    return $columns;
}
add_filter( 'manage_posts_columns', 't21_add_admin_column' );

function t21_show_admin_column( $column, $post_id ) {
    if ( $column !== 'views' ) return;
    
    $views = t21_get_total_views( $post_id );
    
    if ( $views == 0 ) {
        echo '<span style="color:#ccc;">0</span>';
    } elseif ( $views < 100 ) {
        echo '<span>' . number_format_i18n( $views ) . '</span>';
    } else {
        echo '<span style="color:#0073aa;font-weight:bold;">' . number_format_i18n( $views ) . '</span>';
    }
}
add_action( 'manage_posts_custom_column', 't21_show_admin_column', 10, 2 );

/* =========================================
   DATA CLEANUP
   ========================================= */

function t21_cleanup_old_data() {
    if ( ! t21_tables_exist() ) {
        return;
    }
    
    global $wpdb;
    
    $views_table = $wpdb->prefix . 'post_views';
    
    $wpdb->query( $wpdb->prepare(
        "DELETE FROM $views_table WHERE post_id NOT IN (
            SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post'
        )"
    ) );
    
    $last_optimize = get_option( 't21_last_optimize', 0 );
    if ( time() - $last_optimize > 2592000 ) {
        $wpdb->query( "OPTIMIZE TABLE $views_table" );
        update_option( 't21_last_optimize', time() );
    }
}
add_action( 't21_daily_cleanup', 't21_cleanup_old_data' );

if ( t21_tables_exist() && ! wp_next_scheduled( 't21_daily_cleanup' ) ) {
    wp_schedule_event( time(), 'daily', 't21_daily_cleanup' );
}

/* =========================================
   CSS
   ========================================= */

function t21_enqueue_styles() {
    if ( ! is_admin() ) {
        wp_enqueue_style(
            'tv-view-styles',
            get_stylesheet_directory_uri() . '/assets/css/visitas-styles.css',
            [],
            '2.0.0'
        );
    }
}
add_action( 'wp_enqueue_scripts', 't21_enqueue_styles' );
