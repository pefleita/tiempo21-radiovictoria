        </div><!-- .container -->
    </main><!-- #main -->
</div><!-- #page -->

<?php if ( get_theme_mod( 't21_back_to_top', true ) ) : ?>
<button id="back-to-top" class="back-to-top" aria-label="<?php esc_attr_e( 'Volver arriba', 'tiempo21-radiovictoria' ); ?>">
    <i class="fa-solid fa-arrow-up"></i>
</button>
<?php endif; ?>

<footer class="site-footer" id="site-footer">
    <div class="container">

        <!-- Footer Widgets -->
        <?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) ) : ?>
        <div class="footer-widgets">
            <?php for ( $i = 1; $i <= 3; $i++ ) : ?>
                <?php if ( is_active_sidebar( 'footer-' . $i ) ) : ?>
                <div class="footer-widget">
                    <?php dynamic_sidebar( 'footer-' . $i ); ?>
                </div>
                <?php else : ?>
                <div class="footer-widget">
                    <?php if ( $i === 1 ) : ?>
                    <h4 class="footer-widget-title"><?php bloginfo( 'name' ); ?></h4>
                    <p><?php bloginfo( 'description' ); ?></p>
                    <p style="margin-top:.75rem;font-size:.83rem;">Portal informativo de la provincia de Las Tunas, Cuba y el mundo.</p>
                    <?php elseif ( $i === 2 ) : ?>
                    <h4 class="footer-widget-title">Web de Las Tunas</h4>
                    <?php wp_nav_menu( [
                        'theme_location' => 'footer',
                        'container'      => false,
                        'depth'          => 1,
                        'fallback_cb'    => false,
                    ] ); ?>
                    <?php else : ?>
                    <h4 class="footer-widget-title">Radio en Las Tunas</h4>
                    <?php wp_nav_menu( [
                        'theme_location' => 'footer',
                        'container'      => false,
                        'depth'          => 1,
                        'fallback_cb'    => false,
                    ] ); ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <!-- Footer bottom -->
        <div class="footer-bottom">
            <span><?php echo wp_kses_post( get_theme_mod( 't21_footer_copy', '&copy; ' . date( 'Y' ) . ' Tiempo21 - Radio Victoria. Todos los derechos reservados.' ) ); ?></span>
            <?php echo t21_get_social_icons( 'footer-social' ); ?>
        </div>

    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
