<?php get_header(); ?>

<div class="error-404">
    <div class="error-404__code">404</div>
    <h1 class="error-404__title">Página no encontrada</h1>
    <p class="error-404__text">Lo sentimos, la página que buscas no existe o fue movida.</p>
    <?php get_search_form(); ?>
    <p style="margin-top:1.5rem;">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary">
            <i class="fa-solid fa-house"></i> Volver al inicio
        </a>
    </p>
</div>

<?php get_footer(); ?>
