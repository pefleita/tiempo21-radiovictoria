<?php if ( is_active_sidebar( 'sidebar-single' ) ) : ?>
<aside class="sidebar" id="secondary" aria-label="Sidebar">
    <?php dynamic_sidebar( 'sidebar-single' ); ?>
</aside>
<?php else : ?>
<aside class="sidebar" id="secondary" aria-label="Sidebar">

    <!-- Últimas noticias widget por defecto -->
    <div class="widget">
        <h3 class="widget-title"><!-- <i class="fa-solid fa-bolt"></i> --><?php esc_html_e( 'Últimas Noticias', 'tiempo21' ); ?></h3>
        <div class="widget-content">
            <?php
            $sidebar_latest = new WP_Query( [ 'posts_per_page' => 6, 'ignore_sticky_posts' => true, 'no_found_rows' => true ] );
            if ( $sidebar_latest->have_posts() ) : ?>
            <ul>
                <?php while ( $sidebar_latest->have_posts() ) : $sidebar_latest->the_post(); ?>
                <li>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    <div class="date-meta"><i class="fa-regular fa-clock"></i> <?php echo get_the_date(); ?></div>
                </li>
                <?php endwhile; wp_reset_postdata(); ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mas Leidas -->
    <?php
        // ─── MAS LEIDAS (ultimos 30 dias) ─────────────────────────────────────────
        $popular_count = (int) get_theme_mod( 't21_popular_count', 5 );
        $popular_posts = t21_get_popular_posts(30, $popular_count);
    ?>
    <div class="widget">
        <h3 class="widget-title"><!-- <i class="fa-solid fa-fire"></i> --><?php esc_html_e( 'Más Leídas', 'tiempo21' ); ?></h3>
        <div class="widget-content">
            <ul class="side-news-list">
                <?php if ( ! empty( $popular_posts ) ) :
                    $n = 1;
                    foreach ( $popular_posts as $post_item ) : ?>
                    <li class="side-news-item">
                        <span class="side-news-item__num"><?php echo $n; ?></span>
                        <div>
                            <a href="<?php echo esc_url( $post_item['link'] ); ?>" class="side-news-item__title"><?php echo esc_html( $post_item['title'] ); ?></a>
                            <div class="side-news-item__date">
                                <i class="fa-solid fa-eye"></i>
                                <?php echo number_format_i18n( $post_item['views'] ); esc_html_e( ' visitas', 'tiempo21' ); ?>
                            </div>
                        </div>
                    </li>
                    <?php $n++; endforeach;
                else : ?>
                    <li style="padding:.75rem;font-size:.85rem;color:var(--color-text-muted);"><?php esc_html_e( 'Sin datos todavía.', 'tiempo21' ); ?></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

</aside>
<?php endif; ?>
