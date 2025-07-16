<div class="container">
    <div id="barraNavegacioContenidor"></div>
    <main>
        <div class="container contingut">
            <h1>Base de dades persones</h1>
            <h2><span id="nom"></span></h2>

            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['persona']; ?>/modifica-persona/<?php echo $slug; ?>'" class="button btn-gran btn-secondari">Modifica fitxa</button>

            <div class="dadesFitxa">
                <strong>Aquesta fitxa ha estat creada el: </strong><span id="dateCreated"></span> <span id="dateModified"></span>
            </div>

            <div class='fixaDades'>

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
    </main>
</div>