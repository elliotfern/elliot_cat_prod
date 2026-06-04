<?php

use App\Utils\Url;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>
<h1>Base de dades: Persones</h1>

<?php if ($viewModel->isAdmin) : ?>

    <a
        href="<?php echo Url::intranet('persones'); ?>/nova-persona/"
        class="btn btn-secondary btn-sm">
        Afegir autor
    </a>
<?php endif; ?>

<div id="taulaLlistatPersones"></div>