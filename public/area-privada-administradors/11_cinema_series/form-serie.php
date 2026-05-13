<?php
// Obtener la URL completa
$url2 = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($url2);
$path = $parsedUrl['path'];
$segments = explode("/", trim($path, "/"));

if ($segments[2] === "modifica-serie") {
  $modificaBtn = 1;
  $slug = $routeParams[0];
} else {
  $modificaBtn = 2;
}

if ($modificaBtn === 1) {
?>
  <script type="module">
    formUpdate("<?php echo $slug; ?>");
  </script>
<?php
} else {
?>
  <script type="module">
    auxiliarSelect("/api/cinema/get/?directors", "", "director", "nomComplet");
    auxiliarSelect("/api/auxiliars/get/?type=imgSeries", "", "img", "alt");
    auxiliarSelect("/api/auxiliars/get/?type=generesPelis", "", "genre", "genere_ca");
    auxiliarSelect("/api/auxiliars/get/?type=llengues", "", "lang", "idioma_ca");
    auxiliarSelect("/api/auxiliars/get/?type=paisos", "", "country", "pais_cat");
    auxiliarSelect("/api/auxiliars/get/?type=productores", "", "producer", "productora");
  </script>
<?php
}
?>

<div class="barraNavegacio">
  <h6><a href="<?php echo APP_INTRANET; ?>">Intranet</a> > <a href="<?php echo APP_INTRANET . $url['cinema']; ?>">Arts escèniques, cinema i televisió</a> > <a href="<?php echo APP_INTRANET . $url['cinema']; ?>/llistat-series">Llistat sèries tv</a></h6>
</div>

<div class="container-fluid form">
  <?php
  if ($modificaBtn === 1) {
  ?>
    <h2 id="titolSerie"></h2>
  <?php
  } else {
  ?>
    <h2>Creació d'una nova sèrie tv</h2>
  <?php
  }
  ?>
  <div class="alert alert-success" id="missatgeOk" style="display:none" role="alert">
    <h4 class="alert-heading"><strong></strong></h4>
    <h6></h6>
  </div>

  <div class="alert alert-danger" id="missatgeErr" style="display:none" role="alert">
    <h4 class="alert-heading"><strong></strong></h4>
    <h6></h6>
  </div>

  <form method="POST" action="" class="row g-3" id="modificarSerie">
    <?php
    if ($modificaBtn === 1) {
    ?>
      <input type="hidden" name="id" id="id" value="">
    <?php
    }
    ?>

    <div class="col-md-4">
      <label>Nom de la sèrie:</label>
      <input class="form-control" type="text" name="name" id="name" value="">
    </div>

    <div class="col-md-4">
      <label>Slug url:</label>
      <input class="form-control soloNumeros" type="text" name="slug" id="slug" value="">
    </div>

    <div class="col-md-4">
      <label>Any d'inici:</label>
      <input class="form-control soloNumeros" type="text" name="startYear" id="startYear" value="">
    </div>

    <div class="col-md-4">
      <label>Any final:</label>
      <input class="form-control soloNumeros" type="text" name="endYear" id="endYear" value="">
    </div>

    <div class="col-md-4">
      <label>Número de temporades:</label>
      <input class="form-control soloNumeros" type="text" name="season" id="season" value="">
    </div>

    <div class="col-md-4">
      <label>Número de capítols:</label>
      <input class="form-control soloNumeros" type="text" name="chapter" id="chapter" value="">
    </div>

    <div class="col-md-4">
      <label>Director:</label>
      <select class="form-select" name="director" id="director">
      </select>
    </div>

    <div class="col-md-4">
      <label>Productor:</label>
      <select class="form-select" name="producer" id="producer">
      </select>
    </div>

    <div class="col-md-4">
      <label>Imatge:</label>
      <select class="form-select" name="img" id="img">
      </select>
    </div>

    <div class="col-md-4">
      <label>Gènere:</label>
      <select class="form-select" name="genre" id="genre">
      </select>
    </div>

    <div class="col-md-4">
      <label> País:</label>
      <select class="form-select" name="country" id="country">
      </select>
    </div>

    <div class="col-md-4">
      <label>Idioma original:</label>
      <select class="form-select" name="lang" id="lang">
      </select>
    </div>

    <div class="col-md-12">
      <label>Crítica de la pel·lícula:</label>
      <textarea id="descripcio" name="descripcio" rows="4" cols="50" value=""> </textarea>
    </div>

    <div class="container" style="margin-top:25px">
      <div class="row">
        <div class="col-6 text-left">
          <a href="#" onclick="window.history.back()" class="btn btn-secondary">Tornar enrere</a>
        </div>
        <div class="col-6 text-right derecha">
          <?php
          if ($modificaBtn === 1) {
          ?>
            <button type="submit" class="btn btn-primary">Modifica les dades</button>
          <?php
          } else {
          ?>
            <button type="submit" class="btn btn-primary">Crea nova sèrie</button>
          <?php
          }
          ?>
        </div>
      </div>
    </div>
  </form>

</div>

<script>
  function formUpdate(id) {
    let urlAjax = "/api/cinema/get/serieIntranet?=id" + id;

    fetch(urlAjax, {
        method: "GET",
      })
      .then(response => response.json())
      .then(data => {
        // Establecer valores en los campos del formulario
        const newContent = "Sèrie tv: " + data.name;
        const h2Element = document.getElementById('titolSerie');
        h2Element.innerHTML = newContent;

        document.getElementById('name').value = data.name;
        document.getElementById('startYear').value = data.startYear;
        document.getElementById('endYear').value = data.endYear;
        document.getElementById('slug').value = data.slug;
        document.getElementById('chapter').value = data.chapter;
        document.getElementById("id").value = data.id;
        document.getElementById("descripcio").value = data.descripcio;
        document.getElementById("season").value = data.season;

        // Llenar selects con opciones
        auxiliarSelect("/api/cinema/get/?directors", data.idDirector, "director", "nomComplet");
        auxiliarSelect("/api/auxiliars/get/?type=imgSeries", data.idImg, "img", "alt");
        auxiliarSelect("/api/auxiliars/get/?type=generesPelis", data.idGen, "genre", "genere_ca");
        auxiliarSelect("/api/auxiliars/get/?type=llengues", data.idLang, "lang", "idioma_ca");
        auxiliarSelect("/api/auxiliars/get/?type=paisos", data.idPais, "country", "pais_cat");
        auxiliarSelect("/api/auxiliars/get/?type=productores", data.idProductora, "producer", "productora");

      })
      .catch(error => console.error("Error al obtener los datos:", error));
  }

  async function auxiliarSelect(url, selectedValue, selectId, textField) {
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