<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>


<div id="barraNavegacioContenidor"></div>

<h1>Base de dades: Història Oberta</h1>
<div id="infoCurs"></div>

<?php if ($viewModel->isAdmin) : ?>
    ?>
    <button onclick="window.location.href='<?php echo $url['blog']; ?>/nou-curs/'" class="button btn-gran btn-secondari">Nou article</button>
<?php endif; ?>

<div id="llistatArticles" style="margin-top:25px;margin-bottom:30px"></div>