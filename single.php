<?php get_header(); ?>

<div class="with-sidebar">
    <div class="content-area">
        <?php while ( have_posts() ) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class( 'single-article' ); ?>>

            <!-- Categoría -->
            <?php $cats = get_the_category(); if ( $cats ) : ?>
            <div class="single-article__cat">
                <a href="<?php echo esc_url( get_category_link( $cats[0]->term_id ) ); ?>" class="tag-label">
                    <?php echo esc_html( $cats[0]->name ); ?>
                </a>
            </div>
            <?php endif; ?>

            <!-- Título -->
            <h1 class="single-article__title"><?php the_title(); ?></h1>

            <!-- Meta: fecha y autor -->
            <div class="single-article__meta">
                <span><i class="fa-regular fa-clock"></i>
                    <time datetime="<?php the_date( 'c' ); ?>"><?php echo get_the_date(); ?></time>
                </span>
                <span><i class="fa-regular fa-user"></i>
                    <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
                        <?php the_author(); ?>
                    </a>
                </span>
                <?php $views = t21_get_total_views( get_the_ID() ); if ( $views ) : ?>
                <span><i class="fa-regular fa-eye"></i> <?php echo number_format_i18n( $views ); esc_html_e( ' visitas', 'tiempo21' ); ?></span>
                <?php endif; ?>
            </div>

            <!-- Botones Compartir (arriba) -->
            <?php t21_display_share_buttons( 'after_meta' ); ?>

            <!-- Imagen destacada -->
            <?php if ( has_post_thumbnail() ) : ?>
            <figure>
                <?php the_post_thumbnail( 'hero-large', [ 'class' => 'single-article__img', 'loading' => 'eager' ] ); ?>
                <?php if ( get_the_post_thumbnail_caption() ) : ?>
                <figcaption style="font-size:.78rem;color:var(--color-text-muted);margin-top:.3rem;"><?php echo get_the_post_thumbnail_caption(); ?></figcaption>
                <?php endif; ?>
            </figure>
            <?php endif; ?>

            <!-- Breadcrumb -->
            <?php t21_display_breadcrumb(); ?>

            <!-- Contenido -->
            <div class="single-article__content">
                <?php the_content(); ?>
                <?php
                wp_link_pages( [
                    'before' => '<div class="page-links">Páginas:',
                    'after'  => '</div>',
                ] );
                ?>
            </div>

            <!-- Botones Compartir (abajo) -->
            <?php t21_display_share_buttons( 'before_tags' ); ?>

            <!-- Etiquetas -->
            <?php $tags = get_the_tags(); if ( $tags ) : ?>
            <div class="single-article__tags">
                <span class="single-article__tags-label"> Temas:</span>
                <?php foreach ( $tags as $tag ) : ?>
                    <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="tag-pill"><?php echo esc_html( $tag->name ); ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </article>

        <!-- Otras Noticias -->
        <?php
        $related = new WP_Query( [
            'posts_per_page'      => 3,
            'post__not_in'        => [ get_the_ID() ],
            'category__in'        => wp_get_post_categories( get_the_ID() ),
            'ignore_sticky_posts' => true,
            'orderby'             => 'rand',
            'no_found_rows'      => true,
        ] );
        if ( ! $related->have_posts() ) {
            $related = new WP_Query( [
                'posts_per_page'      => 3,
                'post__not_in'        => [ get_the_ID() ],
                'ignore_sticky_posts' => true,
                'no_found_rows'      => true,
            ] );
        }
        ?>
        <?php if ( $related->have_posts() ) : ?>
        <aside class="related-posts" aria-label="Otras noticias">
            <h2 class="section-title"> Otras Noticias</h2>
            <div class="related-posts__grid">
                <?php while ( $related->have_posts() ) : $related->the_post(); ?>
                <article>
                    <a href="<?php the_permalink(); ?>">
                        <?php echo t21_get_thumbnail( get_the_ID(), 'card-small', 'related-item__img' ); ?>
                    </a>
                    <a href="<?php the_permalink(); ?>" class="related-item__title"><?php the_title(); ?></a>
                    <div class="date-meta" style="margin-top:.3rem;font-size:.75rem;">
                        <i class="fa-regular fa-clock"></i>
                        <time datetime="<?php the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                    </div>
                </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </aside>
        <?php endif; ?>

        <!-- Comentarios -->
        <?php if ( comments_open() || get_comments_number() ) :
            comments_template();
        endif; ?>

        <?php endwhile; ?>
    </div><!-- .content-area -->

    <?php get_sidebar(); ?>

</div><!-- .with-sidebar -->

<?php get_footer(); ?>
