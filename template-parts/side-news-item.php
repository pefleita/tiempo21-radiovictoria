<?php
/**
 * Side News Item Template Part
 * Used in latest news and popular posts lists
 */
?>
<a href="<?php the_permalink(); ?>">
    <?php echo t21_get_thumbnail( get_the_ID(), 'thumb-tiny', 'side-news-item__img' ); ?>
</a>
<div>
    <a href="<?php the_permalink(); ?>" class="side-news-item__title"><?php the_title(); ?></a>
</div>
