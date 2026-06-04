<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>


<div id="barraNavegacioContenidor"></div>
<h1>Història Oberta: articles</h1>

<?php if ($viewModel->isAdmin) : ?>
    ?>
    <button onclick="window.location.href='<?php echo $url['blog']; ?>/nou-article/'" class="button btn-gran btn-secondari">Nou article</button>
<?php endif; ?>

<div id="articleList" style="margin-top:25px;margin-bottom:30px"></div>