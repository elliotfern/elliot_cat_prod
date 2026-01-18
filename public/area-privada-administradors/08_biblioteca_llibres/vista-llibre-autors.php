<?php
$slug = $routeParams[0];
?>

<div class="container">
    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">

            <h2>Autors del llibre</h2>
            <h4 id="bookTitle"></h4>

            <div class="alert alert-success" id="missatgeOk" style="display:none"></div>
            <div class="alert alert-danger" id="missatgeErr" style="display:none"></div>

            <div id="isAdminButton" style="display: none;">
                <?php if (isset($_COOKIE['user_id']) && $_COOKIE['user_id'] === '1') : ?>
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
    </main>
</div>