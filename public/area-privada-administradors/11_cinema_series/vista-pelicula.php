<?php
$slug = $routeParams[0];
?>


<div id="barraNavegacioContenidor"></div>

<div class="container">

  <h1>Arts escèniques, cinema i televisió: llistat pel·lícules</h1>

  <div id="isAdminButton" style="display: none;">
    <?php if (isset($_COOKIE['user_id']) && $_COOKIE['user_id'] === '1') : ?>
      <p>

        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['persona']; ?>/nova-persona/'" class="button btn-gran btn-secondari">Afegir actor/a</button>

        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['cinema']; ?>/modifica-pelicula/<?php echo $slug; ?>'" class="button btn-gran btn-secondari">Modifica fitxa</button>
      </p>
    <?php endif; ?>
  </div>

  <div class='fixaDades'>
    <div class='columna imatge'>
      <img id="nameImg" src='' class='img-thumbnail img-fluid rounded mx-auto d-block' style='height:auto;width:auto;max-width:auto' alt='Cartell' title='Cartell'>
    </div>

    <div class="columna">

      <div class="quadre-detalls">
        <p><strong>Títol original: </strong><span id="pelicula"></span></p>
        <p><strong>Títol a Espanya:</strong> <span id="pelicula_es"></span></p>
        <p><strong>Director/a: </strong><a id="directorUrl" href=""><span id="nom"></span> <span id="cognoms"></span></a></p>
        <p><strong>País:</strong> <span id="pais_cat"></span></p>
        <p><strong>Idioma original: </strong><span id="idioma_ca"></span></p>
        <p><strong>Any d'estrena: </strong><span id="any"></span></p>
        <p><strong>Gènere: </strong><span id="genere_ca"></span></p>
        <p><strong>Pel·lícula vista el: </strong><span id="dataVista"></span></p>
        <p><strong>Fitxa creada: </strong><span id="dateCreated"></span></p>
        <p><strong>Fitxa actualizada: </strong> <span id="dateModified"></span></p>
      </div>
    </div>

  </div>
  <hr>
  <div class="container" style="padding:20px;background-color:#ececec;margin-top:25px;margin-bottom:25px">
    <h4>Crítica de la pel·lícula</h4>
    <span id="descripcio"></span>
  </div>

  <hr>

  <h4>Actors:</h4>
  <button onclick="window.location.href='<?php echo APP_INTRANET . $url['cinema']; ?>/inserir-actor-pelicula/<?php echo $slug; ?>'" class="button btn-gran btn-secondari">Afegir actor a la pel·lícula</button>

  <div class="table-responsive">
    <table class="table table-striped" id="booksAuthor">
      <thead class="table-primary">
        <tr>
          <th></th>
          <th>Actor:</th>
          <th>Personatge</th>
          <th></th>
          <th></th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

</div>