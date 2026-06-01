<?php
$slug = $routeParams[0];
?>

<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">
            <h1>Actor/a: <span id="nom"></span> <span id="cognoms"></span></h1>
            <div id="isAdminButton" style="display: none;">
                <?php if (isset($_COOKIE['user_id']) && $_COOKIE['user_id'] === '1') : ?>
                    <p>
                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['persona']; ?>/modifica-persona/<?php echo $slug; ?>'" class="button btn-gran btn-secondari">Modifica fitxa</button>
                    </p>
                <?php endif; ?>
            </div>

            <div class="dadesFitxa">
                <strong>Aquesta fitxa ha estat creada el: </strong><span id="dateCreated"></span> <span id="dateModified"></span>
            </div>

            <div class='fixaDades'>
                <div class='columna imatge'>
                    <img id="nameImg" src='' class='img-thumbnail' style='height:auto;width:auto;max-width:auto' alt='Imatge' title='Imatge'>
                    <span id="alt"></span>
                </div>

                <div class="columna">

                    <div class="quadre-detalls"></div>
                </div>
            </div>

            <hr>
            <h4>Participació a pel·lícules:</h4>

            <div class="table-responsive">
                <table id="taula1" class="table table-striped"></table>
                </table>
            </div>

            <hr>
            <h4>Participació a sèries de televisió:</h4>

            <div class="table-responsive">
                <table class="table table-striped" id="taula2">
                </table>
            </div>

        </div>
    </main>
</div>