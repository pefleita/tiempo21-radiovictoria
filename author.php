<?php get_header(); ?>

<div class="with-sidebar">
    <div class="content-area">

        <?php $author = get_queried_object(); ?>

        <!-- Author box -->
        <div class="author-box">
            <?php echo get_avatar( $author->ID, 80, '', '', [ 'class' => 'author-box__avatar' ] ); ?>
            <div>
                <h1 class="author-box__name"><?php echo esc_html( $author->display_name ); ?></h1>
                <?php if ( $author->description ) : ?>
                <p class="author-box__bio"><?php echo esc_html( $author->description ); ?></p>
                <?php endif; ?>
                <p style="font-size:.82rem;color:var(--color-text-muted);margin-top:.4rem;">
                    <i class="fa-solid fa-newspaper"></i>
                    <?php printf( __( '%s publicaciones', 'tiempo21' ), count_user_posts( $author->ID ) ); ?>
                </p>
            </div>
        </div>

        <!-- Posts del autor -->
        <?php if ( have_posts() ) : ?>

        <?php $layout = get_theme_mod( 't21_posts_layout', 'grid' ); ?>
        <div class="posts-<?php echo esc_attr( $layout ); ?>">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php t21_render_post_card(); ?>
            <?php endwhile; ?>
        </div>

        <?php the_posts_pagination( [
            'prev_text' => '<i class="fa-solid fa-chevron-left"></i>',
            'next_text' => '<i class="fa-solid fa-chevron-right"></i>',
            'class'     => 'pagination',
        ] ); ?>

        <?php else : ?>
        <p style="padding:1rem;text-align:center;color:var(--color-text-muted);">Este autor no tiene publicaciones todavía.</p>
        <?php endif; ?>

    </div>
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
