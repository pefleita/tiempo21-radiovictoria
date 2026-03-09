<?php
/**
 * Video Card Template Part
 * Card for YouTube videos
 * 
 * @param string $vid_url   Video URL
 * @param string $vid_title Video title
 */

$embed_url = t21_youtube_embed( $vid_url );
if ( ! $embed_url ) {
    return;
}
?>
<div class="video-card">
    <div class="video-card__embed">
        <iframe 
            src="<?php echo esc_url( $embed_url ); ?>" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen 
            loading="lazy" 
            title="<?php echo esc_attr( $vid_title ); ?>">
        </iframe>
    </div>
    <p class="video-card__title"><?php echo esc_html( $vid_title ); ?></p>
</div>
