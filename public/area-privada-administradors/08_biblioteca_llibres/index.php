<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Biblioteca de llibres</h1>

<div id="isAdminButton" style="display: none;">
    <?php if ($viewModel->isAdmin) : ?>
        <p>
            <button onclick="window.location.href='/nou-llibre/'" class="button btn-gran btn-secondari">Afegir llibre</button>

            <button onclick="window.location.href='/nova-persona/'" class="button btn-gran btn-secondari">Afegir autor/a</button>
            <button onclick="window.location.href='/nou-grup/'" class="button btn-gran btn-secondari">Afegir grup llibre</button>
        </p>
    <?php endif; ?>
</div>

<div class="alert alert-success quadre">
    <ul class="llistat">
        <!-- Admin: /gestio/... -->
        <li><a href="biblioteca/llistat-llibres">Llistat de llibres</a></li>
        <li><a href="biblioteca/llistat-autors">Llistat d'autors/es</a></li>
        <li><a href="biblioteca/llistat-grups">Llistat de grups de llibres</a></li>

    </ul>
</div>