<?php
$slug = $routeParams[0];
?>

<div class="container">
    <div class="barraNavegacio">
        <h6><a href="<?php echo APP_INTRANET; ?>">Intranet</a> > <a href="<?php echo APP_INTRANET . $url['historia']; ?>">Història</a> > <a href="<?php echo APP_INTRANET . $url['historia']; ?>/llistat-personatges">LListat personatges històrics</a> </h6>
    </div>

    <main>
        <div class="container contingut">
            <h1>Història: <span id="nom"></span></h1>

            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['persona']; ?>/modifica-persona/<?php echo $slug; ?>'" class="button btn-gran btn-secondari">Modifica fitxa</button>

            <div class="dadesFitxa">
                <strong>Aquesta fitxa ha estat creada el: </strong><span id="dateCreated"></span> <span id="dateModified"></span>
            </div>

            <div class='fixaDades'>

                <div class='columna imatge'>
                    <img id="nameImg" src='' class='img-thumbnail' alt='Imatge' title='Imatge'>
                    <p><span id="alt" style="font-size:12px"></span> </p>
                </div>

                <div class="columna">
                    <div class="quadre-detalls"></div>
                </div>
            </div>

            <hr>
            <h4>Càrrecs/responsabilitats:</h4>
            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['historia']; ?>/nou-persona-carrec/<?php echo $slug; ?>'" class="button btn-gran btn-secondari">Afegir càrrec</button>

            <div class="table-responsive">
                <table id="taula1" class="table table-striped"></table>
            </div>

            <hr>
            <h4>Participació en esdeveniments històrics:</h4>

            <div class="table-responsive">
                <table id="taula2" class="table table-striped"></table>
            </div>

        </div>

    </main>
</div>