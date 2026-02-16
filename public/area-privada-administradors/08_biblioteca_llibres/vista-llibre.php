<?php
$slug = $routeParams[0];
?>

<div class="container">
  <div id="barraNavegacioContenidor"></div>

  <main>
    <div class="container contingut">
      <h1>Biblioteca de llibres: <span id="titolBook"></span></h1>

      <div id="isAdminButton" style="display: none;">
        <?php if (isUserAdmin()) :  ?>
          <p>
            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['biblioteca']; ?>/modifica-llibre/<?php echo $slug; ?>'" class="button btn-gran btn-secondari">Modifica fitxa</button>

            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['biblioteca']; ?>/fitxa-llibre-autors/<?php echo $slug; ?>'" class="button btn-gran btn-secondari">Modifica autors del llibre</button>
          </p>
        <?php endif; ?>
      </div>

      <div class="dadesFitxa">
        <strong>Aquesta fitxa ha estat creada el: </strong><span id="dateCreated"></span> <span id="dateModified"></span>
      </div>

      <div class='fixaDades'>

        <div class='columna imatge'>
          <img id="nameImg" src='' class='img-thumbnail img-fluid rounded mx-auto d-block' alt='Llibre Photo' title='Llibre photo'>
        </div>

        <div class="columna">

          <div class="quadre-detalls">
            <p><strong>Títol anglès: </strong> <span id="titolEng"></span></p>
            <div id="linkAutor"></div>
            <p><strong>Any de publicació: </strong> <span id="any"></span></p>
            <p><strong>Editorial: </strong> <span id="editorial"></span></p>
            <p><strong>Gènere: </strong> <span id="genere_cat"></span></p>
            <p><strong>Sub-gènere: </strong> <span id="sub_genere_cat"></span></p>
            <p><strong>Idioma original: </strong> <span id="idioma_ca"></span></p>
            <p><strong>Tipus d'obra: </strong> <span id="nomTipus"></span></p>
            <p><button type='button' class='button btn-petit'><span id="estat"></span></button></p>
          </div>
        </div>
      </div>


    </div>
  </main>
</div>