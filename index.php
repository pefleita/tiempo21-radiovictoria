<?php get_header(); ?>

<div class="with-sidebar">
    <div class="content-area">

        <?php if ( is_home() && ! is_front_page() ) : ?>
        <header class="archive-header">
            <h1 class="archive-title"><?php single_post_title(); ?></h1>
        </header>
        <?php endif; ?>

        <?php if ( have_posts() ) : ?>
            <?php if ( ! is_home() ) : ?>
                <header class="archive-header">
                    <h1 class="archive-title">
                        <?php echo get_the_archive_title('', false); ?>
                    </h1>
                </header>
            <?php endif; ?>
        
        <?php if ( is_category() || is_tag() || is_tax() || is_archive() || is_home() ) : ?>
        <?php t21_display_breadcrumb(); ?>
        <?php endif; ?>
        
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
        <div class="error-404" style="margin:2rem 0;">
            <p class="error-404__title">No se encontraron entradas.</p>
        </div>
        <?php endif; ?>

    </div><!-- .content-area -->
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
