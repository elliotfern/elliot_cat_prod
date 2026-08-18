<?php

use App\Utils\Routes;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Base de dades: organització dades i informació</h1>
<?php if ($viewModel->isAdmin) : ?>

    <div class="container-fluid">

        <h1 class="mb-4">
            <span class="me-2">📂</span>
            Dades
        </h1>

        <div class="row g-3">

            <div class="col-12 col-md-6 col-xl-4">
                <div class="alert alert-secondary quadre mb-0">
                    <a href="<?= Routes::dades()->projectes() ?>" class="text-dark text-decoration-none fw-bold">
                        <span class="me-2">📁</span>
                        10_Projectes
                    </a>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="alert alert-secondary quadre mb-0">
                    <a href="<?= Routes::dades()->documents() ?>" class="text-dark text-decoration-none fw-bold">
                        <span class="me-2">📁</span>
                        20_Documents
                    </a>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="alert alert-secondary quadre mb-0">
                    <a href="<?= Routes::dades()->biblioteca() ?>" class="text-dark text-decoration-none fw-bold">
                        <span class="me-2">📁</span>
                        30_Biblioteca
                    </a>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="alert alert-secondary quadre mb-0">
                    <a href="<?= Routes::dades()->imatges() ?>" class="text-dark text-decoration-none fw-bold">
                        <span class="me-2">📁</span>
                        40_Imatges
                    </a>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="alert alert-secondary quadre mb-0">
                    <a href="<?= Routes::dades()->musica() ?>" class="text-dark text-decoration-none fw-bold">
                        <span class="me-2">📁</span>
                        50_Musica
                    </a>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="alert alert-secondary quadre mb-0">
                    <a href="<?= Routes::dades()->videos() ?>" class="text-dark text-decoration-none fw-bold">
                        <span class="me-2">📁</span>
                        60_Videos
                    </a>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="alert alert-secondary quadre mb-0">
                    <a href="<?= Routes::dades()->baixades() ?>" class="text-dark text-decoration-none fw-bold">
                        <span class="me-2">📁</span>
                        70_Baixades
                    </a>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="alert alert-secondary quadre mb-0">
                    <a href="<?= Routes::dades()->backups() ?>" class="text-dark text-decoration-none fw-bold">
                        <span class="me-2">📁</span>
                        80_Backups
                    </a>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="alert alert-secondary quadre mb-0">
                    <a href="<?= Routes::dades()->escriptori() ?>" class="text-dark text-decoration-none fw-bold">
                        <span class="me-2">📁</span>
                        90_Escriptori
                    </a>
                </div>
            </div>

        </div>

    </div>

<?php endif; ?>