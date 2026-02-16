<?php
$id = $params['id'];
?>

<h2><a href="/contactes">Agenda de contactes</a></h2>

<div class="container-fluid form">
  <h4>Modificació contacte</h4>
  <h5 id="fitxaNomCognoms"></h5>

  <div class="alert alert-success" id="updateContacteMessageOk" style="display:none" role="alert">
    <h4 class="alert-heading"><strong><?php echo ADD_OK_MESSAGE_SHORT; ?></h4></strong>
    <h6><?php echo ADD_OK_MESSAGE; ?></h6>
  </div>

  <div class="alert alert-danger" id="updateContacteMessageErr" style="display:none" role="alert">
    <h4 class="alert-heading"><strong><?php echo ERROR_TYPE_MESSAGE_SHORT; ?></h4></strong>
    <h6><?php echo ERROR_TYPE_MESSAGE; ?></h6>
  </div>

  <form method="POST" action="" id="modalFormUpdateLink" class="row g-3">
    <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">

    <div class="col-md-4">
      <label>Nom:</label>
      <input class="form-control" type="text" name="nom" id="nom" value="">
    </div>

    <div class="col-md-4">
      <label>Cognoms:</label>
      <input class="form-control" type="text" name="cognoms" id="cognoms" value="">
    </div>

    <div class="col-md-4">
      <label>Telèfon 1:</label>
      <input class="form-control" type="text" name="tel_1" id="tel_1" value="">
    </div>

    <div class="col-md-4">
      <label>Telèfon 2:</label>
      <input class="form-control" type="text" name="tel_2" id="tel_2" value="">
    </div>

    <div class="col-md-4">
      <label>Telèfon 3:</label>
      <input class="form-control" type="text" name="tel_3" id="tel_3" value="">
    </div>

    <div class="col-md-4">
      <label>Correu electrònic:</label>
      <input class="form-control" type="text" name="email" id="email" value="">
    </div>

    <div class="col-md-4">
      <label>Adreça:</label>
      <input class="form-control" type="text" name="adreca" id="adreca" value="">
    </div>

    <div class="col-md-4">
      <label>Data naixement:</label>
      <input class="form-control" type="text" name="data_naixement" id="data_naixement" value="">
    </div>

    <div class="col-md-4">
      <label>Pàgina web:</label>
      <input class="form-control" type="text" name="web" id="web" value="">
    </div>

    <div class="col-md-4">
      <label>Tipus de contacte:</label>
      <select class="form-select" name="tipus" id="tipus">
      </select>
    </div>

    <div class="col-md-4">
      <label>País:</label>
      <select class="form-select" name="pais" id="pais">
      </select>
    </div>

    <hr />

    <div class="container">
      <div class="row">
        <div class="col-6 text-left">
          <a href="#" onclick="window.history.back()" class="btn btn-secondary">Anar enrere</a>
        </div>
        <div class="col-6 text-right derecha">
          <button type="submit" onclick="btnModificaContacte(event)" class="btn btn-primary">Modifica contacte</button>
        </div>
      </div>
    </div>


  </form>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Llama a la función fitxaPersonaAfusellat para cargar los datos
    formulariModificaContacte('<?php echo $id; ?>');
  });


  function formulariModificaContacte(slug) {
    let urlAjax = "https://elliot.cat/api/contactes/get/?type=contacte&id=" + slug;
    $.ajax({
      url: urlAjax,
      method: "GET",
      dataType: "json",
      beforeSend: function(xhr) {
        // Obtener el token del localStorage
        let token = localStorage.getItem('token');

        // Incluir el token en el encabezado de autorización
        xhr.setRequestHeader('Authorization', 'Bearer ' + token);
      },

      success: function(data) {
        try {
          let fitxa = data[0];

          // DOM modifications
          document.getElementById('fitxaNomCognoms').innerHTML = fitxa.nom + " " + fitxa.cognoms;

          // 01. dades personals
          document.getElementById('nom').value = fitxa.nom;
          document.getElementById('cognoms').value = fitxa.cognoms;
          document.getElementById('email').value = fitxa.email;
          document.getElementById('tel_1').value = fitxa.tel_1;
          document.getElementById('tel_2').value = fitxa.tel_2;
          document.getElementById('tel_3').value = fitxa.tel_3;
          document.getElementById('adreca').value = fitxa.adreca;
          document.getElementById('data_naixement').value = fitxa.data_naixement;
          document.getElementById('web').value = fitxa.web;
          auxiliarSelect(fitxa.tipus_id, "tipus-contacte", "tipus", "tipus");
          auxiliarSelect(fitxa.pais_id, "paisos", "pais", "country");

          /*nom
cognoms
email
tel_1
tel_2
tel_3
adreca
data_naixement
web
tipus
pais */
        } catch (error) {
          console.error('Error al parsear JSON:', error); // Muestra el error de parsing
        }
      }
    })
  }


  // Carregar el select
  function auxiliarSelect(idAux, api, elementId, valorText) {
    let urlAjax = "https://elliot.cat/api/contactes/get/?type=" + api;
    $.ajax({
      url: urlAjax,
      method: "GET",
      dataType: "json",
      beforeSend: function(xhr) {
        // Obtener el token del localStorage
        let token = localStorage.getItem('token');

        // Incluir el token en el encabezado de autorización
        xhr.setRequestHeader('Authorization', 'Bearer ' + token);
      },

      success: function(data) {
        try {
          // Obtener la referencia al elemento select
          var selectElement = document.getElementById(elementId);

          // Limpiar el select por si ya tenía opciones anteriores
          selectElement.innerHTML = "";

          // Agregar una opción predeterminada "Selecciona una opción"
          var defaultOption = document.createElement("option");
          defaultOption.text = "Selecciona una opció:";
          defaultOption.value = ""; // Valor vacío
          selectElement.appendChild(defaultOption);

          // Iterar sobre los datos obtenidos de la API
          data.forEach(function(item) {
            // Crear una opción y agregarla al select
            // console.log(item.ciutat)
            var option = document.createElement("option");
            option.value = item.id; // Establecer el valor de la opción
            option.text = item[valorText]; // Establecer el texto visible de la opción
            selectElement.appendChild(option);
          });

          // Seleccionar automáticamente el valor
          if (idAux) {
            selectElement.value = idAux;
          }

        } catch (error) {
          console.error('Error al parsear JSON:', error); // Muestra el error de parsing
        }
      }
    })
  }

  function goBack() {
    window.history.back();
  }

  // FUNCIÓ PER TRANSMETRE LES DADES AL SERVIDOR - AJAX/PHP
  function btnModificaContacte() {
    // check values
    $("#updateContacteMessageOk").hide();

    // Stop form from submitting normally
    event.preventDefault();
    let urlAjax = "https://elliot.cat/api/contactes/put/contacte";
    $.ajax({
      type: "POST",
      url: urlAjax,
      dataType: "JSON",
      headers: {
        'X-HTTP-Method-Override': 'PUT'
      },
      beforeSend: function(xhr) {
        // Obtener el token del localStorage
        let token = localStorage.getItem('token');

        // Incluir el token en el encabezado de autorización
        xhr.setRequestHeader('Authorization', 'Bearer ' + token);
      },
      data: {
        nom: $("#nom").val(),
        cognoms: $("#cognoms").val(),
        email: $("#email").val(),
        tel_1: $("#tel_1").val(),
        tel_2: $("#tel_2").val(),
        tel_3: $("#tel_3").val(),
        adreca: $("#adreca").val(),
        data_naixement: $("#data_naixement").val(),
        web: $("#web").val(),
        tipus: $("#tipus").val(),
        pais: $("#pais").val(),
        id: $("#id").val(),
      },
      success: function(response) {
        if (response.status == "success") {
          // Add response in Modal body
          $("#updateContacteMessageOk").show();
          $("#updateContacteMessageErr").hide();
        } else {
          $("#updateContacteMessageErr").show();
          $("#updateContacteMessageOk").hide();
        }
      },
    });
  }
</script>

<?php
# footer
require_once(APP_ROOT . '/public/01_inici/footer.php');
