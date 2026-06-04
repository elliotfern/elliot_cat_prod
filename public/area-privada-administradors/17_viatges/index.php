<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Viatges</h1>

<?php if ($viewModel->isAdmin) : ?>
    <p>
        <button onclick="window.location.href='<?php echo $url['viatges']; ?>/nou-viatge'" class="button btn-gran btn-secondari">Nou viatge</button>

        <button onclick="window.location.href='<?php echo $url['viatges']; ?>/nou-espai'" class="button btn-gran btn-secondari">Nou espai</button>
    </p>
<?php endif; ?>


<div class="alert alert-success quadre">
    <ul class="llistat">
        <li> <a href="<?php echo $url['viatges']; ?>/llistat-viatges">Llistat de viatges</a></li>
        <li><a href="<?php echo $url['viatges']; ?>/llistat-espais">Llistat d'espais</a></li>
        <li><a href="<?php echo $url['viatges']; ?>/llistat-espais-visitats">Llistat d'espais visitats</a></li>
    </ul>
</div>

</div>