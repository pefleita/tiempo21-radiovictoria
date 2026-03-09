<?php
/**
 * Category Mini Item Template Part
 * Small category item with image and title
 */
?>
<li class="cat-mini-item">
    <a href="<?php the_permalink(); ?>">
        <?php echo t21_get_thumbnail( get_the_ID(), 'card-small', 'cat-mini-item__img' ); ?>
    </a>
    <div>
        <a href="<?php the_permalink(); ?>" class="cat-mini-item__title"><?php the_title(); ?></a>
        <div class="date-meta" style="margin-top:.3rem;">
            <i class="fa-regular fa-clock"></i>
            <time datetime="<?php the_date('c'); ?>"><?php echo 'hace ' . human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ); ?></time>
        </div>
    </div>
</li>
