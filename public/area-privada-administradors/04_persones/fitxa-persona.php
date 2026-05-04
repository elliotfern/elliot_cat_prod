<?php

use App\Utils\Url;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$urlModifica = Url::intranet('persones') . '/modifica-persona/' . urlencode($slug);
?>

<div class="container">
    <div id="barraNavegacioContenidor"></div>

    <div class="container contingut">
        <h1>Base de dades Persones</h1>

        <div id="isAdminButton" style="display: none;margin-bottom:25px">
            <?php if (isUserAdmin()) : ?>
                <p>
                    <a href="<?php echo $urlModifica; ?>" class="button btn-gran btn-secondari">Modifica fitxa</a>
                </p>
            <?php endif; ?>
        </div>

        <div class="dadesFitxa" style="background-color: #D4D4D4;padding:15px; border-radius:10px;width:fit-content;">
            <strong>Aquesta fitxa ha estat creada el: </strong><span id="dateCreated"></span> <span id="dateModified"></span>
        </div>

        <div class='fixaDades'>

            <h2><span id="nom"></span></h2>

            <div class='columna imatge'>
                <img id="nameImg" src='' class='img-thumbnail' alt='Imatge' title='Imatge'>
                <p><span id="alt"></span> </p>
            </div>

            <div class="columna">
                <div class="quadre-detalls"></div>
            </div>
        </div>

        <hr>
        <div class="table-responsive">
            <h4><span id="subTaula"></span></h4>
            <div class="table-responsive">
                <table id="taula1" class="table table-striped"></table>
            </div>
        </div>
    </div>
</div>