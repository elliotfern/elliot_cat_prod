<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>
<div id="barraNavegacioContenidor"></div>

<div class="container">

    <h2>Autors del llibre</h2>
    <h4 id="bookTitle"></h4>

    <div class="alert alert-success" id="missatgeOk" style="display:none"></div>
    <div class="alert alert-danger" id="missatgeErr" style="display:none"></div>

    <div id="isAdminButton" style="display: none;">
        <?php if ($viewModel->isAdmin) : ?>
            <p>
                <button
                    id="btnAfegirAutor"
                    type="button"
                    class="button btn-gran btn-secondari">
                    Afegir autor
                </button>

                <button
                    id="btnTornar"
                    type="button"
                    class="btn btn-secondary">
                    Tornar
                </button>
            </p>
        <?php endif; ?>
    </div>

    <div id="authorsTableWrap"></div>

</div>