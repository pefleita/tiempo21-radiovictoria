<?php get_header(); ?>

<?php
// ─── 1. NOTICIA PRINCIPAL (Sticky) ────────────────────────────────────────────
$sticky_ids = get_option( 'sticky_posts' );
$hero_post   = null;
if ( ! empty( $sticky_ids ) ) {
    $hero_query = new WP_Query( [
        'post__in'            => $sticky_ids,
        'posts_per_page'      => 1,
        'ignore_sticky_posts' => true,
        'no_found_rows'      => true,
    ] );
    if ( $hero_query->have_posts() ) {
        $hero_query->the_post();
        $hero_post = get_post();
    }
    wp_reset_postdata();
}

// ─── 2. ÚLTIMAS NOTICIAS ──────────────────────────────────────────────────────
$latest_count = (int) get_theme_mod( 't21_latest_count', 5 );
$exclude_ids  = $hero_post ? [ $hero_post->ID ] : [];
$latest_query = new WP_Query( [
    'posts_per_page'      => $latest_count,
    'post__not_in'        => $exclude_ids,
    'ignore_sticky_posts' => true,
    'no_found_rows'      => true,
] );

// ─── 3. MAS LEIDAS (ultimos 30 dias) ─────────────────────────────────────────
$popular_count = (int) get_theme_mod( 't21_popular_count', 5 );
$popular_posts = t21_get_popular_posts(30, $popular_count);

// ─── Helper: cat section (defined in functions.php) ──────────────────────────
?>

<!-- ═══════════════════════════════════════════════════════════════
     SECCIÓN 1-3: HERO ROW (Noticia Principal + Últimas + Más Leídas)
     ═══════════════════════════════════════════════════════════════ -->
<section class="fp-hero" aria-label="Noticias destacadas">

    <!-- Noticia Principal -->
    <div class="hero-main">
        <?php if ( $hero_post ) : setup_postdata( $hero_post ); ?>
            <a href="<?php the_permalink( $hero_post ); ?>" aria-label="<?php the_title_attribute( [ 'post' => $hero_post ] ); ?>">
                <?php if ( has_post_thumbnail( $hero_post ) ) : ?>
                    <?php echo get_the_post_thumbnail( $hero_post, 'hero-large', [ 'class' => 'hero-main__img', 'loading' => 'eager' ] ); ?>
                <?php else : ?>
                    <div class="hero-main__img img-placeholder" style="min-height:320px;"><i class="fa-solid fa-image" style="font-size:3rem;color:rgba(255,255,255,.3)"></i></div>
                <?php endif; ?>
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
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <div class="hero-main img-placeholder" style="min-height:350px;display:flex;align-items:center;justify-content:center;">
                <p style="color:rgba(255,255,255,.6);font-size:.9rem;text-align:center;padding:1rem;">Fija una entrada como destacada para mostrarla aquí</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Últimas Noticias -->
    <div class="hero-side">
        <h2 class="section-title"><!-- <i class="fa-solid fa-bolt"></i> -->Últimas Noticias</h2>
        <ul class="side-news-list">
            <?php if ( $latest_query->have_posts() ) :
                $n = 1;
                while ( $latest_query->have_posts() ) : $latest_query->the_post(); ?>
                <li class="side-news-item">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail( 'thumb-tiny', [ 'class' => 'side-news-item__img', 'loading' => 'lazy' ] ); ?>
                        </a>
                    <?php endif; ?>
                    <div>
                        <a href="<?php the_permalink(); ?>" class="side-news-item__title"><?php the_title(); ?></a>
                        <!-- <div class="side-news-item__date">
                            <i class="fa-regular fa-clock"></i>
                            <time datetime="<?php //the_date( 'c' ); ?>"><?php //echo 'hace' . human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ); ?></time>
                        </div> -->
                    </div>
                </li>
                <?php $n++; endwhile; wp_reset_postdata();
            else : ?>
                <li style="padding:.75rem;font-size:.85rem;color:var(--color-text-muted);"><?php esc_html_e( 'No hay noticias recientes.', 'tiempo21-radiovictoria' ); ?></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Más Leídas -->
    <div class="hero-side">
        <h2 class="section-title"><!-- <i class="fa-solid fa-fire"></i> --><?php esc_html_e( 'Más Leídas', 'tiempo21-radiovictoria' ); ?></h2>
        <ul class="side-news-list">
            <?php if ( ! empty( $popular_posts ) ) :
                $n = 1;
                foreach ( $popular_posts as $post_item ) : ?>
                <li class="side-news-item">
                    <span class="side-news-item__num"><?php echo $n; ?></span>
                    <div>
                        <a href="<?php echo esc_url( $post_item['link'] ); ?>" class="side-news-item__title"><?php echo esc_html( $post_item['title'] ); ?></a>
                        <div class="side-news-item__date">
                            <i class="fa-solid fa-eye"></i>
                            <?php echo number_format_i18n( $post_item['views'] ); esc_html_e( ' visitas', 'tiempo21-radiovictoria' ); ?>
                        </div>
                    </div>
                </li>
                <?php $n++; endforeach;
            else : ?>
                <li style="padding:.75rem;font-size:.85rem;color:var(--color-text-muted);"><?php esc_html_e( 'Sin datos todavía.', 'tiempo21-radiovictoria' ); ?></li>
            <?php endif; ?>
        </ul>
    </div>

</section>

<!-- ═══════════════════════════════════════════════════════════════
     SECCIÓN 4: IMAGEN-LINKS
     ═══════════════════════════════════════════════════════════════ -->
<section class="fp-image-links" aria-label="Secciones destacadas">
    <?php for ( $i = 1; $i <= 4; $i++ ) :
        $img_id = get_theme_mod( 't21_imglink_img_' . $i );
        $url    = get_theme_mod( 't21_imglink_url_' . $i, '#' );
        $label  = get_theme_mod( 't21_imglink_label_' . $i, 'Sección ' . $i );
    ?>
    <a href="<?php echo esc_url( $url ); ?>" class="img-link-item">
        <?php if ( $img_id ) : ?>
            <?php echo wp_get_attachment_image( $img_id, 'card-medium', false, [ 'loading' => 'lazy', 'alt' => esc_attr( $label ) ] ); ?>
        <?php else : ?>
            <div class="img-placeholder" style="width:100%;height:100%;background:linear-gradient(135deg,#006622,#f5f5f5);min-height:120px;"></div>
        <?php endif; ?>
        <span class="img-link-item__label"><?php echo esc_html( $label ); ?></span>
    </a>
    <?php endfor; ?>
</section>

<!-- ═══════════════════════════════════════════════════════════════
     SECCIONES 5-7: CATEGORÍAS GRANDES (50% + 25% + 25%)
     ═══════════════════════════════════════════════════════════════ -->
<section class="fp-cats-row">

    <!-- Cat 1 (Grande) -->
    <div class="cat-section">
        <h2 class="section-title"><!--<i class="fa-solid fa-newspaper"></i> --><?php echo esc_html( t21_get_category_title(1) ); ?></h2>
        <?php $q1 = t21_get_category_posts(1); ?>
        <?php if ( $q1 && $q1->have_posts() ) : ?>
        <ul class="cat-section__list">
            <?php while ( $q1->have_posts() ) : $q1->the_post(); ?>
            <li class="cat1-item">
                <a href="<?php the_permalink(); ?>">
                    <?php if ( has_post_thumbnail() ) :
                        the_post_thumbnail( 'thumb-tiny', [ 'class' => 'cat1-item__img', 'loading' => 'lazy' ] );
                    else : ?>
                        <div class="cat1-item__img img-placeholder"><i class="fa-solid fa-image"></i></div>
                    <?php endif; ?>
                </a>
                <div class="cat1-item__body">
                    <a href="<?php the_permalink(); ?>" class="cat1-item__title"><?php the_title(); ?></a>
                    <p class="cat1-item__excerpt"><?php echo wp_trim_words( get_the_excerpt(), 50 ); ?></p>
                    <div class="date-meta" style="margin-top:.3rem;"><i class="fa-regular fa-clock"></i> <time datetime="<?php the_date('c'); ?>"><?php echo 'hace ' . human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ); ?></time></div>
                </div>
            </li>
            <?php endwhile; wp_reset_postdata(); ?>
        </ul>
        <?php else : ?>
        <p style="padding:.75rem;font-size:.85rem;color:var(--color-text-muted);">Configure la Categoría 1 en el Personalizador.</p>
        <?php endif; ?>
    </div>

    <!-- Cat 2 (Mediana) -->
    <div class="cat-section">
        <h2 class="section-title"><!--<i class="fa-solid fa-folder-open"></i> --><?php echo esc_html( t21_get_category_title(2) ); ?></h2>
        <?php $q2 = t21_get_category_posts(2); ?>
        <?php if ( $q2 && $q2->have_posts() ) : ?>
        <ul class="cat-section__list">
            <?php while ( $q2->have_posts() ) : $q2->the_post(); ?>
            <li class="cat-mini-item">
                <a href="<?php the_permalink(); ?>">
                    <?php if ( has_post_thumbnail() ) :
                        the_post_thumbnail( 'card-small', [ 'class' => 'cat-mini-item__img', 'loading' => 'lazy' ] );
                    else : ?>
                        <div class="cat-mini-item__img img-placeholder"><i class="fa-solid fa-image"></i></div>
                    <?php endif; ?>
                </a>
                <div>
                    <a href="<?php the_permalink(); ?>" class="cat-mini-item__title"><?php the_title(); ?></a>
                    <div class="date-meta" style="margin-top:.3rem;"><i class="fa-regular fa-clock"></i> <time datetime="<?php the_date('c'); ?>"><?php echo 'hace ' . human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ); ?></time></div>
                </div>
            </li>
            <?php endwhile; wp_reset_postdata(); ?>
        </ul>
        <?php else : ?>
        <p style="padding:.75rem;font-size:.85rem;color:var(--color-text-muted);">Configure la Categoría 2 en el Personalizador.</p>
        <?php endif; ?>
    </div>

    <!-- Cat 3 (Mediana) -->
    <div class="cat-section">
        <h2 class="section-title"><!--<i class="fa-solid fa-folder-open"></i> --><?php echo esc_html( t21_get_category_title(3) ); ?></h2>
        <?php $q3 = t21_get_category_posts(3); ?>
        <?php if ( $q3 && $q3->have_posts() ) : ?>
        <ul class="cat-section__list">
            <?php while ( $q3->have_posts() ) : $q3->the_post(); ?>
            <li class="cat-mini-item">
                <a href="<?php the_permalink(); ?>">
                    <?php if ( has_post_thumbnail() ) :
                        the_post_thumbnail( 'card-small', [ 'class' => 'cat-mini-item__img', 'loading' => 'lazy' ] );
                    else : ?>
                        <div class="cat-mini-item__img img-placeholder"><i class="fa-solid fa-image"></i></div>
                    <?php endif; ?>
                </a>
                <div>
                    <a href="<?php the_permalink(); ?>" class="cat-mini-item__title"><?php the_title(); ?></a>
                    <div class="date-meta" style="margin-top:.3rem;"><i class="fa-regular fa-clock"></i> <time datetime="<?php the_date('c'); ?>"><?php echo 'hace ' . human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ); ?></time></div>
                </div>
            </li>
            <?php endwhile; wp_reset_postdata(); ?>
        </ul>
        <?php else : ?>
        <p style="padding:.75rem;font-size:.85rem;color:var(--color-text-muted);">Configure la Categoría 3 en el Personalizador.</p>
        <?php endif; ?>
    </div>

</section>

<!-- ═══════════════════════════════════════════════════════════════
     SECCIÓN 8: CATEGORÍAS 4, 5, 6, 7 (25% cada una)
     ═══════════════════════════════════════════════════════════════ -->
<div class="fp-cats-row-4">
    <?php for ( $n = 4; $n <= 7; $n++ ) :
        $qn = t21_get_category_posts( $n );
    ?>
    <div class="cat-section">
        <h2 class="section-title"><!--<i class="fa-solid fa-tag"></i> --><?php echo esc_html( t21_get_category_title( $n ) ); ?></h2>
        <?php if ( $qn && $qn->have_posts() ) : ?>
        <ul class="cat-section__list">
            <?php while ( $qn->have_posts() ) : $qn->the_post(); ?>
            <li class="cat-mini-item">
                <a href="<?php the_permalink(); ?>">
                    <?php if ( has_post_thumbnail() ) :
                        the_post_thumbnail( 'card-small', [ 'class' => 'cat-mini-item__img', 'loading' => 'lazy' ] );
                    else : ?>
                        <div class="cat-mini-item__img img-placeholder"><i class="fa-solid fa-image"></i></div>
                    <?php endif; ?>
                </a>
                <div>
                    <a href="<?php the_permalink(); ?>" class="cat-mini-item__title"><?php the_title(); ?></a>
                    <div class="date-meta" style="margin-top:.3rem;"><i class="fa-regular fa-clock"></i> <time datetime="<?php the_date('c'); ?>"><?php echo 'hace ' . human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ); ?></time></div>
                </div>
            </li>
            <?php endwhile; wp_reset_postdata(); ?>
        </ul>
        <?php else : ?>
        <p style="padding:.75rem;font-size:.85rem;color:var(--color-text-muted);">Configure la Categoría <?php echo $n; ?> en el Personalizador.</p>
        <?php endif; ?>
    </div>
    <?php endfor; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     SECCIÓN 9: FOTORREPORTAJES
     ═══════════════════════════════════════════════════════════════ -->
<?php
$photo_cat_slug = get_theme_mod( 't21_photo_cat', 'fotorreportajes' );
$photo_query = new WP_Query( [
    'category_name'       => $photo_cat_slug,
    'posts_per_page'      => 3,
    'ignore_sticky_posts' => true,
    'no_found_rows'      => true,
] );
?>
<section class="fp-photoReports">
    <h2 class="section-title"><!--<i class="fa-solid fa-camera"></i> -->Fotorreportajes</h2>
    <div class="fp-photoReports__grid">
        <?php if ( $photo_query->have_posts() ) :
            while ( $photo_query->have_posts() ) : $photo_query->the_post(); ?>
            <article class="photo-report-card">
                <a href="<?php the_permalink(); ?>">
                    <?php if ( has_post_thumbnail() ) :
                        the_post_thumbnail( 'card-medium', [ 'class' => 'photo-report-card__img', 'loading' => 'lazy' ] );
                    else : ?>
                        <div class="photo-report-card__img img-placeholder" style="min-height:200px;"><i class="fa-solid fa-camera" style="font-size:2.5rem;"></i></div>
                    <?php endif; ?>
                </a>
                <div class="photo-report-card__overlay">
                    <!--<div class="photo-report-card__badge">
                        <i class="fa-solid fa-camera"></i> Fotorreportaje
                    </div>-->
                    <a href="<?php the_permalink(); ?>" class="photo-report-card__title"><?php the_title(); ?></a>
                </div>
            </article>
            <?php endwhile; wp_reset_postdata();
        else : ?>
            <p style="color:var(--color-text-muted);font-size:.88rem;grid-column:1/-1;">Configure la categoría "Fotorreportajes" en el Personalizador (slug: fotorreportajes).</p>
        <?php endif; ?>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════
     SECCIÓN 10: VIDEOS
     ═══════════════════════════════════════════════════════════════ -->
<?php
$has_videos = false;
for ( $v = 1; $v <= 3; $v++ ) {
    if ( get_theme_mod( 't21_video_url_' . $v ) ) { $has_videos = true; break; }
}
?>
<?php if ( $has_videos ) : ?>
<section class="fp-videos">
    <h2 class="section-title"><!--<i class="fa-brands fa-youtube"></i> -->Videos</h2>
    <div class="fp-videos__grid">
        <?php for ( $v = 1; $v <= 3; $v++ ) :
            $vid_url   = get_theme_mod( 't21_video_url_' . $v );
            $vid_title = get_theme_mod( 't21_video_title_' . $v, 'Video ' . $v );
            
            $video_id = '';
            if ( preg_match( '/youtu\.be\/([a-zA-Z0-9_\-]+)/', $vid_url, $m ) ) {
                $video_id = $m[1];
            } elseif ( preg_match( '/youtube\.com\/watch\?v=([a-zA-Z0-9_\-]+)/', $vid_url, $m ) ) {
                $video_id = $m[1];
            } elseif ( preg_match( '/youtube\.com\/embed\/([a-zA-Z0-9_\-]+)/', $vid_url, $m ) ) {
                $video_id = $m[1];
            }
            if ( ! $video_id ) continue;
            
            $thumbnail_url = 'https://img.youtube.com/vi/' . esc_attr( $video_id ) . '/maxresdefault.jpg';
            $embed_url = 'https://www.youtube.com/embed/' . esc_attr( $video_id ); ?>
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
        <?php endfor; ?>
    </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
