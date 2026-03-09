<?php
/**
 * Hero Main Template Part
 * Featured sticky post on front page
 */

if ( ! $hero_post ) {
    return;
}
?>
<a href="<?php the_permalink( $hero_post ); ?>" aria-label="<?php echo esc_attr( get_the_title( $hero_post ) ); ?>">
    <?php echo t21_get_thumbnail( $hero_post->ID, 'hero-large', 'hero-main__img' ); ?>
</a>
<div class="hero-main__overlay">
    <?php $cats = get_the_category( $hero_post ); if ( $cats ) : ?>
        <div class="hero-main__cat">
            <a href="<?php echo esc_url( get_category_link( $cats[0]->term_id ) ); ?>" class="tag-label"><?php echo esc_html( $cats[0]->name ); ?></a>
        </div>
    <?php endif; ?>
    <a href="<?php the_permalink( $hero_post ); ?>" class="hero-main__title"><?php echo get_the_title( $hero_post ); ?></a>
    <div class="hero-main__meta date-meta">
        <i class="fa-regular fa-clock"></i>
        <time datetime="<?php echo get_the_date( 'c', $hero_post ); ?>"><?php echo get_the_date( '', $hero_post ); ?></time>
    </div>
</div>
