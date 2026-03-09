<?php
if ( post_password_required() ) return;
?>
<div id="comments" class="comments-area">

    <?php if ( have_comments() ) : ?>
    <h2 class="comments-title">
        <?php
        $count = get_comments_number();
        printf(
            _n( '%s Comentario', '%s Comentarios', $count, 'tiempo21' ),
            number_format_i18n( $count )
        );
        ?>
    </h2>

    <ol class="comment-list">
        <?php wp_list_comments( [
            'style'       => 'ol',
            'short_ping'  => true,
            'avatar_size' => 40,
            'callback'    => function( $comment, $args, $depth ) {
                ?>
                <li id="comment-<?php comment_ID(); ?>" <?php comment_class( 'comment-item', $comment ); ?>>
                    <div class="comment-container">
                        <div class="comment-author">
                            <?php echo get_avatar( $comment, 40, '', '', [ 'class' => '' ] ); ?>
                            <div>
                                <span class="comment-author-name"><?php comment_author( $comment ); ?></span>
                                <span class="comment-date">
                                    <a href="<?php echo esc_url( get_comment_link( $comment ) ); ?>">
                                        <?php comment_date( '', $comment ); ?> a las <?php comment_time( '', $comment ); ?>
                                    </a>
                                </span>
                            </div>
                        </div>
                        <?php if ( '0' === $comment->comment_approved ) : ?>
                        <p style="font-size:.82rem;color:var(--color-text-muted);font-style:italic;">Tu comentario está pendiente de moderación.</p>
                        <?php endif; ?>
                        <div class="comment-content"><?php comment_text( $comment ); ?></div>
                        <?php comment_reply_link( array_merge( $args, [ 'depth' => $depth, 'max_depth' => $args['max_depth'] ] ) ); ?>
                    </div>
                </li>
                <?php
            },
        ] ); ?>
    </ol>

    <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
    <nav class="pagination" aria-label="Páginas de comentarios">
        <?php paginate_comments_links( [ 'prev_text' => '<i class="fa-solid fa-chevron-left"></i>', 'next_text' => '<i class="fa-solid fa-chevron-right"></i>' ] ); ?>
    </nav>
    <?php endif; ?>

    <?php endif; ?>

    <?php if ( ! comments_open() && get_comments_number() ) : ?>
    <p style="font-size:.88rem;color:var(--color-text-muted);padding:.75rem;background:#f5f5f5;border-radius:4px;">Los comentarios están cerrados.</p>
    <?php endif; ?>

    <?php
    comment_form( [
        'title_reply'          => '<span class="section-title" style="display:block;margin-bottom:1rem;"><i class="fa-regular fa-comment"></i> Deja un comentario</span>',
        'title_reply_to'       => 'Responder a %s',
        'cancel_reply_link'    => 'Cancelar respuesta',
        'label_submit'         => 'Publicar comentario',
        'class_submit'         => 'btn btn-primary',
        'comment_field'        => '<div style="margin-bottom:.85rem;"><label for="comment">' . __( 'Comentario <span aria-required="true">*</span>', 'tiempo21' ) . '</label><textarea id="comment" name="comment" rows="5" required></textarea></div>',
        'comment_notes_before' => '',
        'comment_notes_after'  => '',
        'class_form'           => 'comment-form',
        'fields'               => [
            'author' => '<div class="form-grid"><div><label for="author">' . __( 'Nombre <span aria-required="true">*</span>', 'tiempo21' ) . '</label><input type="text" id="author" name="author" required></div>',
            'email'  => '<div><label for="email">' . __( 'Correo electrónico <span aria-required="true">*</span>', 'tiempo21' ) . '</label><input type="email" id="email" name="email" required></div></div>',
            'url'    => '',
            'cookies'=> '',
        ],
    ] );
    ?>

</div>
