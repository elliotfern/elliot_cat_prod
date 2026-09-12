<?php

use App\Utils\Routes;
use App\Utils\Button;
use App\Utils\Url;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<?php if ($viewModel->isAdmin) : ?>

    <h1>Base de dades: Història Oberta</h1>

    <div class="d-flex flex-wrap gap-2 my-3">
        <?=
        Button::create('Crear esdeveniment', Routes::historia()->nouEsdeveniment()) .
            Button::create('Crear persona', Routes::persona()->novaPersona()) .
            Button::create('Crear article', Routes::blog()->nouArticle()) ?>
    </div>

    <div class="alert alert-success quadre">

        <h4>Història oberta:</h4>
        <ul>
            <li><a href="<?php echo Url::intranet('historia'); ?>/llistat-cursos">Llistat de cursos</a></li>
            <li><a href="<?php echo Url::intranet('historia'); ?>/llistat-articles">Llistat d'articles</a></li>
            <li><a href="<?php echo Url::intranet('historia'); ?>/llistat-slots-cursos-articles">Llistat d'articles-cursos</a></li>
        </ul>

        <h4>Història:</h4>
        <ul>
            <li><a href="<?php echo Url::intranet('persones'); ?>/llistat-persones">Llistat de persones</a></li>
            <li><a href="<?php echo Url::intranet('historia'); ?>/llistat-organitzacions">Llistat d'organitzacions</a></li>
            <li><a href="<?php echo Url::intranet('historia'); ?>/llistat-esdeveniments">Llistat d'esdeveniments</a></li>
        </ul>
    </div>

<?php endif; ?>