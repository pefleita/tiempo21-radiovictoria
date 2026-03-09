<?php get_header(); ?>

<div class="with-sidebar">
    <div class="content-area">

        <header class="archive-header">
            <h1 class="archive-title">
                <i class="fa-solid fa-magnifying-glass"></i>
                Resultados para: <em><?php echo esc_html( get_search_query() ); ?></em>
            </h1>
            <?php if ( have_posts() ) : ?>
            <p class="archive-desc"><?php printf( __( '%d resultados encontrados', 'tiempo21' ), $wp_query->found_posts ); ?></p>
            <?php endif; ?>
        </header>

        <?php get_search_form(); ?>

        <?php if ( have_posts() ) : ?>

        <?php $layout = get_theme_mod( 't21_posts_layout', 'grid' ); ?>
        <div class="posts-<?php echo esc_attr( $layout ); ?>">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php t21_render_post_card(); ?>
            <?php endwhile; ?>
        </div>

        <?php the_posts_pagination( [
            'prev_text' => '<i class="fa-solid fa-chevron-left"></i>',
            'next_text' => '<i class="fa-solid fa-chevron-right"></i>',
            'class'     => 'pagination',
        ] ); ?>

        <?php else : ?>
        <div style="padding:2rem;text-align:center;background:var(--color-bg-white);border-radius:var(--radius-md);box-shadow:var(--shadow);">
            <i class="fa-solid fa-magnifying-glass" style="font-size:2.5rem;color:var(--color-border);margin-bottom:1rem;"></i>
            <p style="font-size:1rem;color:var(--color-text-muted);">No se encontraron resultados para tu búsqueda. Intenta con otros términos.</p>
        </div>
        <?php endif; ?>

    </div>
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
