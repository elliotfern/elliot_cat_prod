<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>


<h1>Taules auxiliars</h1>
<?php if ($viewModel->isAdmin) : ?>
    <p>
        <button onclick="window.location.href='<?php echo $url['auxiliars']; ?>/nova-imatge/'" class="button btn-gran btn-secondari">Afegir imatge</button>

        <button onclick="window.location.href='<?php echo $url['auxiliars']; ?>/nova-ciutat/'" class="button btn-gran btn-secondari">Afegir ciutat</button>

        <button onclick="window.location.href='<?php echo $url['auxiliars']; ?>/nou-pais/'" class="button btn-gran btn-secondari">Afegir país</button>

        <button onclick="window.location.href='<?php echo $url['auxiliars']; ?>/nou-grup/'" class="button btn-gran btn-secondari">Afegir grup/professió</button>

        <button onclick="window.location.href='<?php echo $url['auxiliars']; ?>/nou-tema/'" class="button btn-gran btn-secondari">Afegir tema</button>

        <button onclick="window.location.href='<?php echo $url['auxiliars']; ?>/nou-subtema/'" class="button btn-gran btn-secondari">Afegir subtema</button>
    </p>
<?php endif; ?>

<div class="alert alert-success quadre">
    <ul class="llistat">
        <li><a href="<?php echo $url['auxiliars']; ?>/llistat-imatges">Llistat d'imatges</a></li>
        <li><a href="<?php echo $url['auxiliars']; ?>/llistat-ciutats">Llistat de ciutats</a></li>
        <li><a href="<?php echo $url['auxiliars']; ?>/llistat-paisos">Llistat de paisos</a></li>
        <li><a href="<?php echo $url['auxiliars']; ?>/llistat-grups">Llistat de grups / professions de persones</a></li>
        <li><a href="<?php echo $url['auxiliars']; ?>/llistat-temes">Llistat temes</a></li>
        <li><a href="<?php echo $url['auxiliars']; ?>/llistat-subtemes">Llistat de subtemes</a></li>
    </ul>
</div>