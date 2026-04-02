<?php
/**
 * Category 1 Item Template Part
 * Large category item with image, title, excerpt, date
 */
?>
<li class="cat1-item">
    <a href="<?php the_permalink(); ?>">
        <?php echo t21_get_thumbnail( get_the_ID(), 'thumb-tiny', 'cat1-item__img' ); ?>
    </a>
    <div class="cat1-item__body">
        <a href="<?php the_permalink(); ?>" class="cat1-item__title"><?php the_title(); ?></a>
        <?php 
        $post = get_post();
        $excerpt = has_excerpt() ? get_the_excerpt() : $post->post_content;
        $content = strip_tags( $excerpt );
        ?>
        <p class="cat1-item__excerpt"><?php echo wp_trim_words( $content, 50 ); ?></p>
        <div class="date-meta" style="margin-top:.3rem;">
            <i class="fa-regular fa-clock"></i>
            <time datetime="<?php the_date('c'); ?>"><?php echo 'hace ' . human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ); ?></time>
        </div>
    </div>
</li>
