<?php get_header(); ?>

<div class="with-sidebar">
    <div class="content-area">

        <header class="archive-header">
            <h1 class="archive-title">Blog</h1>
        </header>

        <?php
        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
        $args = array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 12,
            'paged'          => $paged,
        );
        $blog_query = new WP_Query( $args );
        ?>

        <?php if ( $blog_query->have_posts() ) : ?>
        <?php $layout = get_theme_mod( 't21_posts_layout', 'grid' ); ?>
        <div class="posts-<?php echo esc_attr( $layout ); ?>">
            <?php while ( $blog_query->have_posts() ) : $blog_query->the_post(); ?>
                <?php t21_render_post_card(); ?>
            <?php endwhile; ?>
        </div>

        <?php
        $total_pages = $blog_query->max_num_pages;
        if ( $total_pages > 1 ) {
            $current_page = max( 1, get_query_var( 'paged' ) );
            echo paginate_links( array(
                'base'    => get_pagenum_link( 1 ) . '%_%',
                'format'  => 'page/%#%',
                'current' => $current_page,
                'total'   => $total_pages,
                'prev_text' => '<i class="fa-solid fa-chevron-left"></i>',
                'next_text' => '<i class="fa-solid fa-chevron-right"></i>',
                'class'     => 'pagination',
            ) );
        }
        ?>

        <?php wp_reset_postdata(); ?>

        <?php else : ?>
        <div class="error-404" style="margin:2rem 0;">
            <p class="error-404__title">No se encontraron entradas.</p>
        </div>
        <?php endif; ?>

    </div><!-- .content-area -->
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
