<div class="barraNavegacio">
  <h6><a href="<?php echo APP_INTRANET; ?>">Intranet</a> > <a href="<?php echo APP_INTRANET . $url['auxiliars']; ?>">Auxiliars</a> > <a href="<?php echo APP_INTRANET . $url['auxiliars']; ?>/llistat-imatges">Llistat imatges</a> </h6>
</div>

<div class="container-fluid form">
  <h2>Auxiliars: pujar nova imatge</h2>

  <div class="alert alert-success" id="missatgeOk" style="display:none">
  </div>

  <div class="alert alert-danger" id="missatgeErr" style="display:none;">
  </div>

  <form method="POST" action="" id="uploadImgForm" class="row g-3">

    <?php $timestamp = date('Y-m-d'); ?>
    <input type="hidden" name="dateCreated" id="dateCreated" value="'<?php echo $timestamp; ?>'">

    <div class="col-md-4">
      <label>Nom:</label>
      <input class="form-control" type="text" name="nom" id="nom">
      <label style="color:#dc3545">* Obligatori </label>
    </div>

    <div class="col-md-4">
      <label>Categoria de la imatge:</label>
      <select class="form-select" id="typeImg" name="typeImg">
        <option selected>Selecciona el tipus d'imatge</option>
        <option value="1">Biblioteca llibres: autor</option>
        <option value="2">Biblioteca llibres: llibre</option>
        <option value="3">Història: imatge</option>
        <option value="4">Història: esdeveniment</option>
        <option value="5">Història: persona</option>
        <option value="6">Història: organització</option>
        <option value="12">Història: mapa</option>
        <option value="15">Història: infografia</option>
        <option value="16">Història: cronologia</option>
        <option value="10">Història: thumbnail</option>
        <option value="7">Cinema: sèrie tv</option>
        <option value="8">Cinema: pel·lícula</option>
        <option value="9">Cinema: actor</option>
        <option value="14">Cinema: director</option>
        <option value="11">Viatges: viatge</option>
        <option value="17">Viatges: espai</option>
        <option value="13">Blog</option>
        <option value="18">Usuaris: avatars</option>
        <option value="19">Web: icones</option>
        <option value="20">CV: logos empreses</option>
      </select>
    </div>

    <div class="col-md-4">
    </div>

    <div class="col-md-12">
      <label>Descripció de la imatge:</label>
      <textarea id="alt" name="alt" rows="5" cols="50" value=""> </textarea>
    </div>

    <div class="col-md-4">
      <label>Fitxer:</label>
      <input class="form-control" type="file" id="fileToUpload" name="fileToUpload">
    </div>

    <div class="col-md-4">
    </div>

    <div class="col-md-4">
    </div>


    <div class="container" style="margin-top:25px">
      <div class="row">
        <div class="col-6 text-left">
          <a href="#" onclick="window.history.back()" class="btn btn-secondary">Tornar enrere</a>
        </div>
        <div class="col-6 text-right derecha">
          <button type="submit" class="btn btn-primary" id="btnUploadImage">Penjar imatge</button>
        </div>
      </div>
    </div>
  </form>

</div>

<script>
  document.querySelector("#uploadImgForm").addEventListener("submit", submitUploadImg);

  async function submitUploadImg(event) {
    event.preventDefault(); // Evita que el formulario se envíe normalmente

    // Limpiar mensajes anteriores
    document.getElementById("missatgeOk").style.display = 'none';
    document.getElementById("missatgeErr").style.display = 'none';

    // Verificar si el archivo está seleccionado
    const fileInput = document.getElementById("fileToUpload");
    if (!fileInput.files.length) {
      alert("Por favor, selecciona una imagen para subir.");
      return; // Detener el envío si no hay archivo seleccionado
    }

    // Crear un FormData con el formulario
    let formData = new FormData(document.querySelector("#uploadImgForm"));
    let urlAjax = "/api/auxiliars/post/imatges"; // La URL de la API

    try {
      const response = await fetch(urlAjax, {
        method: "POST",
        body: formData, // Enviar el formulario con la imagen
      });

      const result = await response.json(); // Parsear la respuesta como JSON

      // Asegurarnos de que el div de error no esté visible antes de mostrar el éxito
      document.getElementById("missatgeErr").style.display = 'none';
      document.getElementById("missatgeOk").style.display = 'none';

      // Comprobar el resultado de la respuesta
      if (result.status === "success") {
        const missatgeOk = document.getElementById('missatgeOk');
        missatgeOk.style.display = 'block'; // Mostrar mensaje de éxito
        missatgeOk.innerHTML = `<strong>Imatge penjada amb èxit!</strong>`;

        // Limpiar el formulario (si quieres dejar todos los campos vacíos)
        limpiarFormulario("uploadImgForm");

        // Eliminar el mensaje de éxito después de 5 segundos
        setTimeout(() => {
          missatgeOk.style.display = 'none';
        }, 5000);

      } else {
        // Mostrar mensaje de error
        document.getElementById("missatgeErr").style.display = 'block';
        document.getElementById("missatgeErr").innerHTML = `<strong>Error, tornar-ho a provar</strong>`;
      }
    } catch (error) {
      console.error("Error uploading image:", error);
      // Mostrar mensaje de error en caso de fallo
      document.getElementById("missatgeErr").style.display = 'block';
      document.getElementById("missatgeErr").innerHTML = `<strong>Error, tornar-ho a provar</strong>`;
    }
  }

  function limpiarFormulario(formId) {
    const formulario = document.getElementById(formId);
    const inputs = formulario.querySelectorAll('input, textarea, select');
    inputs.forEach((input) => {
      input.value = ''; // Limpiar el valor del campo
      if (input.type === "file") {
        input.value = null; // Limpiar campo de archivo
      }
      if (input.selectedIndex !== undefined) {
        input.selectedIndex = 0; // Limpiar el select (poner el primer valor por defecto)
      }
    });
  }
</script>