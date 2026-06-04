<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>


<div id="barraNavegacioContenidor"></div>

<h1>Gestió usuaris web</h1>

<?php if ($viewModel->isAdmin) : ?>
    <p>
        <button onclick="window.location.href='<?php echo $url['usuaris']; ?>/nou-usuari'" class="button btn-gran btn-secondari">Nou usuari</button>
    </p>
<?php endif; ?>
</div>

<div class="alert alert-success quadre">
    <ul class="llistat">
        <li> <a href="<?php echo $url['usuaris']; ?>/llistat-usuaris">Llistat d'usuaris</a></li>
    </ul>
</div>

</div>