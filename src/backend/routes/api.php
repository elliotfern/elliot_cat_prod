<?php

// Rutas principales sin idioma explícito (solo para el idioma por defecto)
$routes = [
    // API: Registre usuari / Login
    '/api/auth/login' => [
        'view' => 'src/backend/api/00_auth/get/get-login.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/auth/get' => [
        'view' => 'src/backend/api/00_auth/get/get-auth.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/auth/get/usuaris' => [
        'view' => 'src/backend/api/00_auth/get/get-usuaris.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/auth/get/usuari/{id}' => [
        'view' => 'src/backend/api/00_auth/get/get-usuari.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/auth/post/usuari' => [
        'view' => 'src/backend/api/00_auth/post/post-usuari.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/auth/put/usuari' => [
        'view' => 'src/backend/api/00_auth/put/put-usuari.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/auth/get/nomUsuari' => [
        'view' => 'src/backend/api/00_auth/get/get-nom-usuari.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    // API: Vault
    '/api/vault/get' => [
        'view' => 'src/backend/api/10_vault/get-vault.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/vault/post' => [
        'view' => 'src/backend/api/10_vault/post-vault.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/vault/put' => [
        'view' => 'src/backend/api/10_vault/put-vault.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    // API COMPTABILITAT ERP
    '/api/accounting/proxy' => [
        'view' => 'src/backend/api/02_accounting/proxy.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/accounting/get' => [
        'view' => 'src/backend/api/02_accounting/accounting.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/accounting/post/invoice' => [
        'view' => 'src/backend/api/02_accounting/customer-invoice-insert.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/accounting/get/invoice-pdf/{id}' => [
        'view' => 'src/backend/api/02_accounting/generate_pdf.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    // 08. BIBLIOTECA
    '/api/biblioteca/get' => [
        'view' => 'src/backend/api/08_biblioteca_llibres/get-library.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/biblioteca/put' => [
        'view' => 'src/backend/api/08_biblioteca_llibres/put-biblioteca.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/biblioteca/post' => [
        'view' => 'src/backend/api/08_biblioteca_llibres/post-biblioteca.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    // BD CINEMA
    '/api/cinema/get/{slug}' => [
        'view' => 'src/backend/api/11_cinema/get-cinema.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/cinema/post' => [
        'view' => 'src/backend/api/11_cinema/post-cinema.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/cinema/put' => [
        'view' => 'src/backend/api/11_cinema/put-cinema.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    // BD AUXILIARS
    '/api/auxiliars/post/imatges' => [
        'view' => 'src/backend/api/100_auxiliars/image-upload-process-form.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    // BD AUXILIARS
    '/api/auxiliars/get' => [
        'view' => 'src/backend/api/100_auxiliars/get-auxiliars.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/auxiliars/get/{slug}' => [
        'view' => 'src/backend/api/100_auxiliars/get-auxiliars.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    // XARXES SOCIALS
    '/api/xarxes-socials/post/bluesky' => [
        'view' => 'src/backend/api/12_xarxes_socials/post-bluesky.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/xarxes-socials/get/mastodont' => [
        'view' => 'src/backend/api/12_xarxes_socials/get-mastodon.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/xarxes-socials/post/mastodont' => [
        'view' => 'src/backend/api/12_xarxes_socials/post-mastodont.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/xarxes-socials/post/likes-mastodont' => [
        'view' => 'src/backend/api/12_xarxes_socials/post-like-mastodont.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/xarxes-socials/post/blog' => [
        'view' => 'src/backend/api/12_xarxes_socials/post-blog.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    // LECTOR RSS
    '/api/lector-rss/get' => [
        'view' => 'src/backend/api/14_lector_rss/get-lector.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    // SITEMAP
    '/api/sitemap/get' => [
        'view' => 'src/backend/api/15_sitemap/get-sitemap.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    // HISTORIA OBERTA
    '/api/historia/get' => [
        'view' => 'src/backend/api/16_historia_oberta/get-historia.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/historia/post' => [
        'view' => 'src/backend/api/16_historia_oberta/post-historia.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/historia/put' => [
        'view' => 'src/backend/api/16_historia_oberta/put-historia.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    // AGENDA CONTACTES
    '/api/contactes/get' => [
        'view' => 'src/backend/api/contactes/get-contactes.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    // 09. Adreces interes
    '/api/adreces/get' => [
        'view' => 'src/backend/api/09_adreces/get-link.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/adreces/post' => [
        'view' => 'src/backend/api/09_adreces/post-link.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/adreces/put' => [
        'view' => 'src/backend/api/09_adreces/put-link.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    // 04. Persones
    '/api/persones/get' => [
        'view' => 'src/backend/api/04_persones/get-persones.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    // 17. VIATGES
    '/api/viatges/get' => [
        'view' => 'src/backend/api/17_viatges/get-viatges.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/viatges/post' => [
        'view' => 'src/backend/api/17_viatges/post-viatges.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/viatges/put' => [
        'view' => 'src/backend/api/17_viatges/put-viatges.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    // Blog
    '/api/blog/get' => [
        'view' => 'src/backend/api/13_blog/get-blog.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/blog/post' => [
        'view' => 'src/backend/api/13_blog/post-blog.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/blog/put' => [
        'view' => 'src/backend/api/13_blog/put-blog.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/radio/get' => [
        'view' => 'src/backend/api/19_radio/get-radio.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    // Curriculum
    '/api/curriculum/get/{slug}' => [
        'view' => 'src/backend/api/20_curriculum/get-cv.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/curriculum/post/{slug}' => [
        'view' => 'src/backend/api/20_curriculum/post-cv.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

    '/api/curriculum/put/{slug}' => [
        'view' => 'src/backend/api/20_curriculum/put-cv.php',
        'needs_session' => false,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => true
    ],

];

return $routes;
