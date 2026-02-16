<h2><a href="/contactes">Agenda de contactes</a></h2>

<div class="container-fluid form">
  <h4>Crear nou contacte</h4>

  <div class="alert alert-success" id="updateContacteMessageOk" style="display:none" role="alert">
    <h4 class="alert-heading"><strong><?php echo ADD_OK_MESSAGE_SHORT; ?></h4></strong>
    <h6><?php echo ADD_OK_MESSAGE; ?></h6>
  </div>

  <div class="alert alert-danger" id="updateContacteMessageErr" style="display:none" role="alert">
    <h4 class="alert-heading"><strong><?php echo ERROR_TYPE_MESSAGE_SHORT; ?></h4></strong>
    <h6><?php echo ERROR_TYPE_MESSAGE; ?></h6>
  </div>

  <form method="POST" action="" id="modalFormUpdateLink" class="row g-3">

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
          <a href="#" onclick="window.history.back()" class="btn btn-secondary">Tornar enrere</a>
        </div>
        <div class="col-6 text-right derecha">
          <button type="submit" onclick="btnNouContacte(event)" class="btn btn-primary">Crear contacte</button>
        </div>
      </div>
    </div>


  </form>
</div>

<script>
  auxiliarSelect("", "tipus-contacte", "tipus", "tipus");
  auxiliarSelect("", "paisos", "pais", "country");


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
  function btnNouContacte(event) {
    // check values
    $("#updateContacteMessageOk").hide();

    // Stop form from submitting normally
    event.preventDefault();
    let urlAjax = "https://elliot.cat/api/contactes/post";
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
