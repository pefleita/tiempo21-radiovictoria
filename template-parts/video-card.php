<?php
/**
 * Video Card Template Part
 * Card for YouTube videos - Lite Embed (Facade Pattern)
 * 
 * @param string $vid_url   Video URL
 * @param string $vid_title Video title
 */

$video_id = '';
if ( preg_match( '/youtu\.be\/([a-zA-Z0-9_\-]+)/', $vid_url, $m ) ) {
    $video_id = $m[1];
} elseif ( preg_match( '/youtube\.com\/watch\?v=([a-zA-Z0-9_\-]+)/', $vid_url, $m ) ) {
    $video_id = $m[1];
} elseif ( preg_match( '/youtube\.com\/embed\/([a-zA-Z0-9_\-]+)/', $vid_url, $m ) ) {
    $video_id = $m[1];
}

if ( ! $video_id ) {
    return;
}

$thumbnail_url = 'https://img.youtube.com/vi/' . esc_attr( $video_id ) . '/maxresdefault.jpg';
$embed_url = 'https://www.youtube.com/embed/' . esc_attr( $video_id );
?>
<div class="video-card">
    <div class="video-card__embed">
        <div class="video-lite-embed" data-video-id="<?php echo esc_attr( $video_id ); ?>" data-embed-url="<?php echo esc_url( $embed_url ); ?>">
            <img 
                src="<?php echo esc_url( $thumbnail_url ); ?>" 
                alt="<?php echo esc_attr( $vid_title ); ?>"
                loading="lazy"
            >
            <button class="video-lite-embed__play" aria-label="Reproducir video <?php echo esc_attr( $vid_title ); ?>">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                    <path d="M8 5v14l11-7z"/>
                </svg>
            </button>
        </div>
    </div>
    <p class="video-card__title"><?php echo esc_html( $vid_title ); ?></p>
</div>
