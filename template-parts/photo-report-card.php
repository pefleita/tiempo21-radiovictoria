<?php
/**
 * Photo Report Card Template Part
 * Card for photo reports/fotorreportajes
 */
?>
<article class="photo-report-card">
    <a href="<?php the_permalink(); ?>">
        <?php echo t21_get_thumbnail( get_the_ID(), 'card-medium', 'photo-report-card__img' ); ?>
    </a>
    <div class="photo-report-card__overlay">
        <a href="<?php the_permalink(); ?>" class="photo-report-card__title"><?php the_title(); ?></a>
    </div>
</article>
