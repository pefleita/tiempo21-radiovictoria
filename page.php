<?php
// page.php
get_header(); ?>

<div class="with-sidebar">
    <div class="content-area">
        <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'single-article' ); ?>>
            <h1 class="single-article__title"><?php the_title(); ?></h1>
            
            <!-- Breadcrumb -->
            <?php t21_display_breadcrumb(); ?>
            
            <?php if ( has_post_thumbnail() ) : ?>
            <?php the_post_thumbnail( 'hero-large', [ 'class' => 'single-article__img', 'loading' => 'eager' ] ); ?>
            <?php endif; ?>
            
            <!-- Botones Compartir (arriba) -->
            <?php t21_display_share_buttons( 'after_meta' ); ?>
            
            <div class="single-article__content">
                <?php the_content(); ?>
                <?php wp_link_pages(); ?>
            </div>
            
            <!-- Botones Compartir (abajo) -->
            <?php t21_display_share_buttons( 'before_tags' ); ?>
        </article>
        <?php if ( comments_open() || get_comments_number() ) : comments_template(); endif; ?>
        <?php endwhile; ?>
    </div>
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
