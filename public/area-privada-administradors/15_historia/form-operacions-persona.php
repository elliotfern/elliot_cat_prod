<?php
// Obtener la URL completa
$url2 = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($url2);
$path = $parsedUrl['path'];
$segments = explode("/", trim($path, "/"));

if ($segments[2] === "modifica-persona") {
  $modificaBtn = 1;
  $autorSlug = $routeParams[0];
} else {
  $modificaBtn = 2;
}

if ($modificaBtn === 1) {
?>
  <script type="module">
    formUpdateAuthor("<?php echo $autorSlug; ?>");
  </script>
<?php
} else {
?>
  <script type="module">
    // Llenar selects con opciones
    selectOmplirDades("/api/biblioteca/get/?type=auxiliarImatgesAutor", "", "img", "alt");
    selectOmplirDades("/api/biblioteca/get/?type=professio", "", "ocupacio", "professio_ca");
    selectOmplirDades("/api/biblioteca/get/?type=pais", "", "paisAutor", "pais_cat");
    selectOmplirDades("/api/biblioteca/get/?type=grup", "", "grup", "grup_ca");
    selectOmplirDades("/api/biblioteca/get/?type=sexe", "", "sexe", "genereCa");
    selectOmplirDades("/api/biblioteca/get/?type=ciutat", "", "ciutatNaixement", "ciutat");
    selectOmplirDades("/api/biblioteca/get/?type=ciutat", "", "ciutatDefuncio", "ciutat");
    selectOmplirDades("/api/biblioteca/get/?type=calendariDies", "", "diaNaixement", "dia");
    selectOmplirDades("/api/biblioteca/get/?type=calendariDies", "", "diaDefuncio", "dia");
    selectOmplirDades("/api/biblioteca/get/?type=calendariMesos", "", "mesNaixement", "mes");
    selectOmplirDades("/api/biblioteca/get/?type=calendariMesos", "", "mesDefuncio", "mes");
  </script>
<?php
}
?>

<div class="barraNavegacio">
  <h6><a href="<?php echo APP_INTRANET; ?>">Intranet</a> > <a href="<?php echo APP_INTRANET . $url['persona']; ?>">Base de dades Persones</a></h6>
</div>

<div class="container-fluid form">
  <?php
  if ($modificaBtn === 1) {
  ?>
    <h2>Base de dades de persones: modificació de dades</h2>
    <h4 id="authorUpdateTitle"></h4>
  <?php
  } else {
  ?>
    <h2>Base de dades de persones: creació d'un nou registre</h2>
  <?php
  }
  ?>

  <div class="alert alert-success" id="missatgeOk" style="display:none" role="alert">
  </div>

  <div class="alert alert-danger" id="missatgeErr" style="display:none" role="alert">
  </div>

  <form method="POST" action="" class="row g-3" id="modificaAutor">
    <input type="hidden" name="id" id="id" value="">

    <div class="col-md-4">
      <label>Nom:</label>
      <input class="form-control" type="text" name="nom" id="nom" value="">
    </div>

    <div class="col-md-4">
      <label>Cognoms:</label>
      <input class="form-control" type="text" name="cognoms" id="cognoms" value="">
    </div>

    <div class="col-md-4">
      <label>Slug:</label>
      <input class="form-control" type="text" name="slug" id="slug" value="">
    </div>

    <div class="col-md-4">
      <label>Gènere:</label>
      <select class="form-select" name="sexe" id="sexe">
      </select>
    </div>

    <div class="col-md-4">
      <label>Pàgina web:</label>
      <input class="form-control" type="url" name="web" id="web" value="">
    </div>

    <div class="col-md-4">
    </div>

    <div class="col-md-4">
      <label>Dia de naixement:</label>
      <select class="form-select" name="diaNaixement" id="diaNaixement">
      </select>
    </div>

    <div class="col-md-4">
      <label>Mes de naixement:</label>
      <select class="form-select" name="mesNaixement" id="mesNaixement">
      </select>
    </div>

    <div class="col-md-4">
      <label>Any de naixement:</label>
      <input class="form-control" type="text" name="anyNaixement" id="anyNaixement" value="">
    </div>

    <div class="col-md-4">
      <label>Dia de defunció:</label>
      <select class="form-select" name="diaDefuncio" id="diaDefuncio">
      </select>
    </div>

    <div class="col-md-4">
      <label>Mes de defunció:</label>
      <select class="form-select" name="mesDefuncio" id="mesDefuncio">
      </select>
    </div>

    <div class="col-md-4">
      <label>Any de defunció:</label>
      <input class="form-control" type="text" name="anyDefuncio" id="anyDefuncio" value="">
    </div>

    <div class="col-md-4">
      <label>Ciutat naixement:</label>
      <select class="form-select" name="ciutatNaixement" id="ciutatNaixement">
      </select>
    </div>

    <div class="col-md-4">
      <label>Ciutat defunció:</label>
      <select class="form-select" name="ciutatDefuncio" id="ciutatDefuncio">
      </select>
    </div>

    <div class="col-md-4">
      <label>Professió</label>
      <select class="form-select" name="ocupacio" id="ocupacio">
      </select>
    </div>

    <div class="col-md-4">
      <label>País:</label>
      <select class="form-select" name="paisAutor" id="paisAutor">
      </select>
    </div>

    <div class="col-md-4">
      <label>Imatge:</label>
      <select class="form-select" name="img" id="img">
      </select>
    </div>

    <div class="col-md-4">
      <label>Classificació grup persona:</label>
      <select class="form-select" name="grup" id="grup">
      </select>
    </div>

    <div class="col-md-4">
    </div>

    <div class="col-md-4">
    </div>

    <div class="col-complet">
      <label for="AutDescrip" class="form-label">Descripció (català):</label>
      <textarea class="form-control" id="descripcio" name="descripcio" rows="10"></textarea>
    </div>

    <div class="col-complet">
      <label for="AutDescrip" class="form-label">Descripció (castellà):</label>
      <textarea class="form-control" id="descripcioCast" name="descripcioCast" rows="10"></textarea>
    </div>


    <div class="col-complet">
      <label for="AutDescrip" class="form-label">Descripció (anglès):</label>
      <textarea class="form-control" id="descripcioEng" name="descripcioEng" rows="10"></textarea>
    </div>


    <div class="col-complet">
      <label for="AutDescrip" class="form-label">Descripció (italià):</label>
      <textarea class="form-control" id="descripcioIt" name="descripcioIt" rows="10"></textarea>
    </div>

    <div class="container">
      <div class="row">
        <div class="col-6 text-left">
          <a href="#" onclick="window.history.back()" class="btn btn-secondary">Tornar enrere</a>
        </div>
        <div class="col-6 text-right derecha">
          <?php
          if ($modificaBtn === 1) {
          ?>
            <button type="submit" class="btn btn-primary">Actualitza persona</button>
          <?php
          } else {
          ?>
            <button type="submit" class="btn btn-primary">Crea nou persona</button>
          <?php
          }
          ?>

        </div>
      </div>
    </div>


  </form>
</div>

<script>
  function formUpdateAuthor(id) {
    let urlAjax = "/api/persones/get/?persona=" + id;

    fetch(urlAjax, {
        method: "GET",
      })
      .then(response => response.json())
      .then(data => {
        // Establecer valores en los campos del formulario

        // sexe, ciutatNaixement, ciutatDefuncio,
        document.getElementById("nom").value = data.nom;
        document.getElementById("cognoms").value = data.cognoms;
        document.getElementById("slug").value = data.slug;
        document.getElementById("web").value = data.web;
        document.getElementById("anyNaixement").value = data.anyNaixement;
        document.getElementById("anyDefuncio").value = data.anyDefuncio;
        document.getElementById("descripcio").innerHTML = decodeURIComponent(data.descripcio);
        document.getElementById("descripcioCast").innerHTML = decodeURIComponent(data.descripcioCast);
        document.getElementById("descripcioEng").innerHTML = decodeURIComponent(data.descripcioEng);
        document.getElementById("descripcioIt").innerHTML = decodeURIComponent(data.descripcioIt);
        document.getElementById("id").value = data.id;

        const h2Element = document.getElementById("authorUpdateTitle");
        h2Element.innerHTML = `Autor: ${data.nom} ${data.cognoms}`;

        // Llenar selects con opciones
        selectOmplirDades("/api/biblioteca/get/?type=auxiliarImatgesAutor", data.idImg, "img", "alt");
        selectOmplirDades("/api/biblioteca/get/?type=professio", data.idOcupacio, "ocupacio", "professio_ca");
        selectOmplirDades("/api/biblioteca/get/?type=pais", data.idPais, "paisAutor", "pais_cat");
        selectOmplirDades("/api/biblioteca/get/?type=grup", data.idGrup, "grup", "grup_ca");
        selectOmplirDades("/api/biblioteca/get/?type=sexe", data.idSexe, "sexe", "genereCa");
        selectOmplirDades("/api/biblioteca/get/?type=ciutat", data.idCiutatNaixement, "ciutatNaixement", "ciutat");
        selectOmplirDades("/api/biblioteca/get/?type=ciutat", data.idCiutatDefuncio, "ciutatDefuncio", "ciutat");
        selectOmplirDades("/api/biblioteca/get/?type=calendariDies", data.diaNaixement, "diaNaixement", "dia");
        selectOmplirDades("/api/biblioteca/get/?type=calendariDies", data.diaDefuncio, "diaDefuncio", "dia");
        selectOmplirDades("/api/biblioteca/get/?type=calendariMesos", data.mesNaixement, "mesNaixement", "mes");
        selectOmplirDades("/api/biblioteca/get/?type=calendariMesos", data.mesDefuncio, "mesDefuncio", "mes");
      })
      .catch(error => console.error("Error al obtener los datos:", error));
  }

  async function selectOmplirDades(url, selectedValue, selectId, textField) {
    try {
      const response = await fetch(url);
      if (!response.ok) {
        throw new Error('Error en la sol·licitud AJAX');
      }

      const data = await response.json();
      const selectElement = document.getElementById(selectId);
      if (!selectElement) {
        console.error(`Select element with id ${selectId} not found`);
        return;
      }

      // Netejar les opcions actuals
      selectElement.innerHTML = '';

      // Afegir la opció predeterminada "Selecciona un gènere"
      const defaultOption = document.createElement('option');
      defaultOption.value = ''; // El valor predeterminat es un string vacío
      defaultOption.textContent = 'Selecciona una opció';
      selectElement.appendChild(defaultOption);

      // Afegir les noves opcions
      data.forEach((item) => {
        const option = document.createElement('option');
        option.value = item.id;
        option.text = item[textField];
        if (item.id === selectedValue) {
          option.selected = true;
        }
        selectElement.appendChild(option);
      });
    } catch (error) {
      console.error('Error:', error);
    }
  }
</script>