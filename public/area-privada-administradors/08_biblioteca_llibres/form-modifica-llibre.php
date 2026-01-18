<?php
// Obtener la URL completa
$url2 = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($url2);
$path = $parsedUrl['path'];
$segments = explode("/", trim($path, "/"));

if ($segments[2] === "modifica-llibre") {
  $modificaBtn = 1;
  $slug = $routeParams[0];
} else {
  $modificaBtn = 2;
}

if ($modificaBtn === 1) {
?>
  <script type="module">
    formUpdateLlibre("<?php echo $slug; ?>");
  </script>
<?php
} else {
?>
  <script type="module">
    // Llenar selects con opciones
    selectOmplirDades("/api/biblioteca/get/?type=imatgesLlibres", "", "img", "alt");
    selectOmplirDades("/api/biblioteca/get/?type=temes", "", "sub_tema_id", "tema_complet");
    selectOmplirDades("/api/biblioteca/get/?type=llengues", "", "lang", "idioma_ca");
    selectOmplirDades("/api/biblioteca/get/?type=estatLlibre", "", "estat", "estat");
    selectOmplirDades("/api/biblioteca/get/?type=editorials", "", "editorial_id", "editorial");
    selectOmplirDades("/api/biblioteca/get/?type=tipus", "", "tipus_id", "nomTipus");
  </script>
<?php
}
?>

<div class="barraNavegacio">
  <h6><a href="<?php echo APP_INTRANET; ?>">Intranet</a> > <a href="<?php echo APP_INTRANET . $url['biblioteca']; ?>">Biblioteca</a> > <a href="<?php echo APP_INTRANET . $url['biblioteca']; ?>/llistat-llibres">Llibres </a></h6>
</div>

<div class="container-fluid form">
  <?php
  if ($modificaBtn === 1) {
  ?>
    <h2>Modificar les dades del llibre</h2>
    <h4 id="bookUpdateTitle"></h4>
  <?php
  } else {
  ?>
    <h2>Creació d'un nou llibre</h2>
  <?php
  }
  ?>

  <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
    <div id="okText"></div>
  </div>

  <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
    <div id="errText"></div>
  </div>

  <form id="modificaLlibre" class="row g-3" novalidate>
    <?php $timestamp = date('Y-m-d'); ?>
    <?php
    if ($modificaBtn === 1) {
    ?>
      <input type="hidden" id="id" name="id" value="<?php echo $llibreId; ?>">
    <?php
    }
    ?>

    <div class="col-md-4">
      <label>Títol original:</label>
      <input class="form-control" type="text" name="titol" id="titol" value="">
    </div>

    <div class="col-md-4">
      <label>Slug:</label>
      <input class="form-control" type="text" name="slug" id="slug" value="">
    </div>

    <div class="col-md-4">
      <label>Imatge coberta:</label>
      <select class="form-select" name="img" id="img" value="">
      </select>
    </div>

    <div class="col-md-4">
      <label>Any de publicació:</label>
      <input class="form-control" type="text" name="any" id="any" value="">
    </div>

    <div class="col-md-4">
      <label> Editorial:</label>
      <select class="form-select" name="editorial_id" id="editorial_id"></select>
      </select>
    </div>

    <div class="col-md-4">
      <label>Tema:</label>
      <select class="form-select" name="sub_tema_id" id="sub_tema_id" value="">
      </select>
    </div>

    <div class="col-md-4">
      <label>Idioma:</label>
      <select class="form-select" name="lang" id="lang" value="">
      </select>
    </div>

    <div class="col-md-4">
      <label>Tipus:</label>
      <select class="form-select" name="tipus_id" id="tipus_id"></select>
      </select>
    </div>

    <div class="col-md-4">
      <label>Estat del llibre:</label>
      <select class="form-select" name="estat" id="estat">
      </select>
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
            <button type="submit" class="btn btn-primary">Modifica llibre</button>
          <?php
          } else {
          ?>
            <button type="submit" class="btn btn-primary">Crea nou llibre</button>
          <?php
          }
          ?>

        </div>
      </div>
    </div>
  </form>

</div>

<script>
  function formUpdateLlibre(slug) {
    const urlAjax = "/api/biblioteca/get/?llibreSlug=" + encodeURIComponent(slug);

    fetch(urlAjax, {
        method: "GET"
      })
      .then(r => r.json())
      .then(json => {
        const data = json && json.data ? json.data : json; // compat si algún día no viene wrapper

        // Título arriba
        const h2Element = document.getElementById('bookUpdateTitle');
        if (h2Element) h2Element.innerHTML = "Llibre: " + (data.titol ?? '');

        // Campos reales de db_llibres
        const titolEl = document.getElementById('titol');
        if (titolEl) titolEl.value = data.titol ?? '';

        const slugEl = document.getElementById('slug');
        if (slugEl) slugEl.value = data.slug ?? '';

        const anyEl = document.getElementById('any');
        if (anyEl) anyEl.value = data.any ?? '';

        const idEl = document.getElementById('id');
        if (idEl) idEl.value = data.id ?? '';

        // SELECTS (nombres según db_llibres)
        // OJO: según tu respuesta actual, editorial_id / tipus_id / sub_tema_id vienen como UUID string
        // y lang/img/estat como int.
        selectOmplirDades("/api/biblioteca/get/?type=imatgesLlibres", data.img, "img", "alt");
        selectOmplirDades("/api/biblioteca/get/?type=editorials", data.editorial_id, "editorial_id", "editorial");
        selectOmplirDades("/api/biblioteca/get/?type=temes", data.sub_tema_id, "sub_tema_id", "tema_complet");
        selectOmplirDades("/api/biblioteca/get/?type=llengues", data.lang, "lang", "idioma_ca");
        selectOmplirDades("/api/biblioteca/get/?type=tipus", data.tipus_id, "tipus_id", "nomTipus");
        selectOmplirDades("/api/biblioteca/get/?type=estatLlibre", data.estat, "estat", "estat");
      })
      .catch(err => console.error("Error al obtener los datos:", err));
  }

  async function selectOmplirDades(url, selectedValue, selectId, textField) {
    try {
      const response = await fetch(url);
      if (!response.ok) throw new Error('Error en la sol·licitud AJAX');

      const json = await response.json();
      const items = Array.isArray(json) ? json : (Array.isArray(json.data) ? json.data : []);

      const selectElement = document.getElementById(selectId);
      if (!selectElement) {
        console.error(`Select element with id ${selectId} not found`);
        return;
      }

      selectElement.innerHTML = '';

      const placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = '— Selecciona —';
      selectElement.appendChild(placeholder);

      const selectedStr = selectedValue == null ? '' : String(selectedValue);

      items.forEach((item) => {
        const option = document.createElement('option');

        // Normalmente tu API devuelve item.id (UUID string o int)
        option.value = item.id != null ? String(item.id) : '';

        let label = '';
        if (typeof textField === 'function') label = textField(item);
        else label = item && item[textField] != null ? String(item[textField]) : '';

        option.textContent = label;

        if (option.value === selectedStr) option.selected = true;

        selectElement.appendChild(option);
      });
    } catch (error) {
      console.error('Error:', error);
    }
  }
</script>