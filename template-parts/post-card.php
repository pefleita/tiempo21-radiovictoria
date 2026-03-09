<?php
/**
 * Post Card Template Part
 * Used in blog, archive, search pages
 */

$show_image = get_theme_mod( 't21_posts_show_image', true );
$show_category = get_theme_mod( 't21_posts_show_category', true );
$show_title = get_theme_mod( 't21_posts_show_title', true );
$show_excerpt = get_theme_mod( 't21_posts_show_excerpt', true );
$show_date = get_theme_mod( 't21_posts_show_date', true );
$show_author = get_theme_mod( 't21_posts_show_author', false );
$excerpt_length = get_theme_mod( 't21_posts_excerpt_length', 15 );
?>
<article <?php post_class( 'card' ); ?>>
    <?php if ( $show_image ) : ?>
    <a href="<?php the_permalink(); ?>">
        <?php echo t21_get_thumbnail( get_the_ID(), 'card-medium', 'card__img' ); ?>
    </a>
    <?php endif; ?>
    
    <div class="card__body">
        <?php if ( $show_category ) : ?>
            <?php $cats = get_the_category(); if ( $cats ) : ?>
            <a href="<?php echo esc_url( get_category_link( $cats[0]->term_id ) ); ?>" class="tag-label"><?php echo esc_html( $cats[0]->name ); ?></a>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if ( $show_title ) : ?>
            <a href="<?php the_permalink(); ?>" class="card__title"><?php the_title(); ?></a>
        <?php endif; ?>
        
        <?php if ( $show_excerpt ) : ?>
            <p class="card__excerpt"><?php echo wp_trim_words( get_the_excerpt(), $excerpt_length ); ?></p>
        <?php endif; ?>
        
        <?php if ( $show_date || $show_author ) : ?>
            <div class="date-meta" style="margin-top:.4rem;">
                <?php if ( $show_date ) : ?>
                    <span><i class="fa-regular fa-clock"></i> <?php echo get_the_date(); ?></span>
                <?php endif; ?>
                <?php if ( $show_author ) : ?>
                    <span><i class="fa-regular fa-user"></i> <?php the_author(); ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</article>
