<?php
// Obtener la URL completa
$url2 = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($url2);
$path = $parsedUrl['path'];
$segments = explode("/", trim($path, "/"));

if ($segments[2] === "modifica-actor-serie") {
  $modificaBtn = 1;
  $slug = $routeParams[0];
} else {
  $modificaBtn = 2;
  $slug = $routeParams[0];
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
    formInserir("<?php echo $slug; ?>");
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
    <h2>Modificar relació actor-sèrie tv</h2>
    <h4 id="titolserie"></h4>
  <?php
  } else {
  ?>
    <h2>Creació d'una nova relació entre actor i sèrie tv</h2>
    <h4 id="titolserie"></h4>
  <?php
  }
  ?>

  <div class="alert alert-success" id="missatgeOk" style="display:none">
  </div>

  <div class="alert alert-danger" id="missatgeErr" style="display:none;">
  </div>


  <form method="POST" action="" id="inserirActorSerie" class="row g-3">

    <?php
    if ($modificaBtn === 1) {
    ?>
      <input type="hidden" name="id" id="id" value="">
    <?php
    }
    ?>

    <div class="col-md-4">
      <label>Actor/a:</label>
      <select class="form-select" name="idActor" id="idActor">
      </select>
    </div>

    <div class="col-md-4">
      <label>Sèrie tv:</label>
      <select class="form-select" name="idSerie" id="idSerie">
      </select>
    </div>

    <div class="col-md-4">
      <label>Rol:</label>
      <input class="form-control" type="text" name="role" id="role">
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
            <button type="submit" class="btn btn-primary">Crea</button>
          <?php
          }
          ?>
        </div>
      </div>
    </div>
  </form>

</div>

<script>
  function formInserir(id) {
    let urlAjax = "/api/cinema/get/?serie=" + id;

    fetch(urlAjax, {
        method: "GET",
      })
      .then(response => response.json())
      .then(data => {
        // Establecer valores en los campos del formulario
        const newContent = "Sèrie tv: " + data.name;
        const h2Element = document.getElementById('titolserie');
        h2Element.innerHTML = newContent;

        // Llenar selects con opciones
        auxiliarSelect("/api/cinema/get/?series", data.id, "idSerie", "name");
        auxiliarSelect("/api/cinema/get/?actors", "", "idActor", "nomComplet");

      })
      .catch(error => console.error("Error al obtener los datos:", error));
  }

  function formUpdate(id) {
    let urlAjax = "/api/cinema/get/?actorSerie=" + id;

    fetch(urlAjax, {
        method: "GET",
      })
      .then(response => response.json())
      .then(data => {
        // Establecer valores en los campos del formulario
        const newContent = "Sèrie tv: " + data[0].name;
        const h2Element = document.getElementById('titolserie');
        h2Element.innerHTML = newContent;

        document.getElementById("role").value = data[0].role;
        document.getElementById("id").value = data[0].id;

        // Llenar selects con opciones
        auxiliarSelect("/api/cinema/get/?series", data[0].idSerie, "idSerie", "name");
        auxiliarSelect("/api/cinema/get/?actors", data[0].idActor, "idActor", "nomComplet");

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