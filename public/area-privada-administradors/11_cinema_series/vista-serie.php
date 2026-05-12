<?php
$slug = $routeParams[0];
?>

<div class="container contingut">

  <div id="barraNavegacioContenidor"></div>

  <main>
    <div class="container contingut">
      <h1>Sèrie tv: <span id="name"></span></h1>

      <div id="isAdminButton" style="display: none;">
        <?php if (isset($_COOKIE['user_id']) && $_COOKIE['user_id'] === '1') : ?>
          <p>
            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['cinema']; ?>/modifica-serie/<?php echo $slug; ?>'" class="button btn-gran btn-secondari">Modifica fitxa</button>
          </p>
        <?php endif; ?>
      </div>

      <div class="dadesFitxa">
        <strong>Aquesta fitxa ha estat creada el: </strong><span id="dateCreated"></span> <span id="dateModified"></span>
      </div>

      <div class='fixaDades'>

        <div class='columna imatge'>
          <img id="img" src='' class='img-thumbnail img-fluid rounded mx-auto d-block' style='height:auto;width:auto;max-width:auto' alt='Cartell' title='Cartell'>
        </div>

        <div class="columna">
          <div class="quadre-detalls">
            <p><strong>Creador: </strong><a id="directorUrl" href=""><span id="nom"></span> <span id="cognoms"></span></a></p>
            <p><strong>Idioma original: </strong><span id="idioma_ca"></span></p>
            <p><strong>Gènere: </strong><span id="genere_ca"></span></p>
            <p><strong>País: </strong><span id="pais_cat"></span></p>
            <p><strong>Productora tv/plataforma: </strong><span id="productora"></span></p>
            <p><strong>Número de temporades: </strong><span id="season"></span></p>
            <p><strong>Número d'episodis: </strong><span id="chapter"></span></p>
            <p><span id="startYear"></span></p>
          </div>
        </div>

      </div>

      <hr>
      <div class="container" style="padding:20px;background-color:#ececec;margin-top:25px;margin-bottom:25px">
        <h4>Crítica de la sèrie</h4>
        <span id="descripcio"></span>
      </div>

      <hr>
      <h4>Actors:</h4>
      <button onclick="window.location.href='<?php echo APP_INTRANET . $url['cinema']; ?>/inserir-actor-serie/<?php echo $slug; ?>'" class="button btn-gran btn-secondari">Afegir actor a la sèrie</button>

      <div class="table-responsive" id="actors-container">

      </div>

    </div>
  </main>
</div>

<script>
  connexioApiGetDades("/api/cinema/get/serie?serieSlug=", "<?php echo $slug; ?>")
  actorsDeLaSerie("<?php echo $slug; ?>");

  async function connexioApiGetDades(url, id) {
    const urlAjax = `${url}${id}`;

    try {
      const response = await fetch(urlAjax, {
        method: 'GET'
      });

      if (!response.ok) {
        throw new Error('Error en la sol·licitud AJAX');
      }

      const data = await response.json();

      // Asegúrate de que data sea un objeto o array adecuado
      const data2 = Array.isArray(data) ? data[0] : data;

      for (let key in data2) {
        if (data2.hasOwnProperty(key)) {
          let value = data2[key];

          // Buscar el elemento `<span>` con el ID correspondiente
          const element = document.getElementById(key);
          if (element) {
            // Verificar que el elemento es un `<span>` antes de modificar
            if (element.tagName === 'SPAN') {
              // Decodificar HTML si es necesario y asignar solo el texto
              element.textContent = value; // Solo reemplazar el contenido del `span`
            }
          }

          // Actualizar el DOM con la información recibida
          if (key === 'nameImg') {
            document.getElementById('img').src = `https://media.elliot.cat/img/cinema-serie/${data2['nameImg']}.jpg`;
          }

          // Casos especiales: Director/a
          if (key === 'nom' || key === 'cognoms') {
            const directorUrl = document.getElementById('directorUrl');
            if (directorUrl && directorUrl.tagName === 'A') {
              directorUrl.href = `${window.location.origin}/gestio/cinema/fitxa-director/${data2['slugDirector']}`; // Añadir la URL del director
            }
          }

          // Anys naixement i defuncio
          if (key === 'anyNaixement' || key === 'anyDefuncio') {
            const dateElement = document.getElementById(key);
            if (dateElement && dateElement.tagName === 'SPAN') {
              const anyNaixement = parseInt(data2['anyNaixement'], 10);
              const anyDefuncio = data2['anyDefuncio'] ? parseInt(data2['anyDefuncio'], 10) : null;
              const anyActual = new Date().getFullYear();

              let edad;

              if (!anyDefuncio) {
                edad = anyActual - anyNaixement; // Calcula la edad actual si sigue vivo
              } else {
                edad = anyDefuncio - anyNaixement; // Calcula la edad en caso de fallecimiento
              }

              dateElement.innerHTML = `${anyNaixement} - ${anyDefuncio || ""} (${edad} anys)`;
            }
          }

          // Anys naixement i defuncio
          if (key === 'dateCreated') {
            const dateElement = document.getElementById('dateCreated');
            if (dateElement && dateElement.tagName === 'SPAN') {
              const dateObj = new Date(value);
              const day = dateObj.getDate();
              const month = dateObj.getMonth() + 1; // Los meses van de 0 a 11
              const year = dateObj.getFullYear();

              dateElement.textContent = `${day}/${month}/${year}`;
            }
          }

          if (key === 'dateModified') {
            const dateElement = document.getElementById('dateModified');

            // Verifica si el valor es distinto de '0000-00-00' y si la fecha es válida
            if (dateElement && dateElement.tagName === 'SPAN') {
              const dateObj = new Date(value);

              // Verifica si la fecha es válida
              if (data2['dateModified'] == '0000-00-00') {
                dateElement.textContent = ''; // No mostrar nada si no es válida
              } else {
                const day = dateObj.getDate();
                const month = dateObj.getMonth() + 1;
                const year = dateObj.getFullYear();

                dateElement.innerHTML = `| <strong> Darrera modificació: </strong> ${day}/${month}/${year}`;

              }
            }
          }

          // Anys naixement i defuncio
          if (key === 'startYear') {
            const dateElement = document.getElementById('startYear');
            if (data2['endYear'] === null || data2['endYear'] === 0) {
              const startYear = data2['startYear'];
              dateElement.innerHTML = `<strong>En emissió: </strong>des de l'any ${startYear}`;
            } else if (data2['startYear'] === data2['endYear']) {
              const startYear = data2['startYear'];
              dateElement.innerHTML = `<strong>Any emissió: </strong> ${startYear}`;
            } else {
              const endYear = data2['endYear'];
              const startYear = data2['startYear'];
              dateElement.innerHTML = `<strong>Anys emissió: </strong>${startYear} / ${endYear}`;
            }
          }
        }
      }

    } catch (error) {
      console.error('Error al parsear JSON:', error); // Muestra el error de parsing
    }
  }

  async function actorsDeLaSerie(id) {
    const urlAjax = `/api/cinema/get/?llistat-actors-serie=${id}`;
    const container = document.getElementById("actors-container"); // Un contenedor para meter la tabla o el mensaje

    try {
      const response = await fetch(urlAjax, {
        method: "GET",
      });

      if (!response.ok) {
        throw new Error(`Error en la petición: ${response.statusText}`);
      }

      const data = await response.json();

      if (data.length > 0) {
        const tableHTML = `
        <table class="table table-striped" id="actors">
          <thead class="table-primary">
            <tr>
              <th></th>
              <th>Actor</th>
              <th>Personatge</th>
              <th></th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            ${data.map(actor => `
              <tr>
                <td>
                  <a id="actor-${actor.idActor}" title="Actor" href="${window.location.origin}/gestio/cinema/fitxa-actor/${actor.slug}">
                    <img src="https://media.elliot.cat/img/cinema-actor/${actor.nameImg}.jpg" width="100" height="auto">
                  </a>
                </td>
                <td>
                  <a id="actor-${actor.idActor}" title="Actor" href="${window.location.origin}/gestio/cinema/fitxa-actor/${actor.slug}">
                    ${actor.nom} ${actor.cognoms}
                  </a>
                </td>
                <td>${actor.role}</td>
                <td>
                  <a href="${window.location.origin}/gestio/cinema/modifica-actor-serie/${actor.idCast}" class="btn btn-secondary btn-sm modificar-link">Modificar</a>
                </td>
                <td>
                  <button type="button">Elimina</button>
                </td>
              </tr>
            `).join("")}
          </tbody>
        </table>
      `;

        container.innerHTML = tableHTML;

      } else {
        container.innerHTML = `<p class="text-muted">No hi ha actors assignats a aquesta sèrie.</p>`;
      }

    } catch (error) {
      console.error("Error al obtener los actores:", error);
    }
  }
</script>

<style>
  .row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-top: 20px;
    margin-bottom: 30px;
    gap: 20px;
    flex-wrap: wrap;
  }

  .col {
    flex: 1;
    margin: 2px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
  }

  .imatge {
    flex: 0 0 300px;
    /* Limita el ancho de la primera columna a 200px */
  }


  .img-thumbnail {
    max-width: 300px;
    height: auto !important;
    border-radius: 8px;
  }

  .quadre-detalls {
    border: 1px black;
  }

  /* Media query para pantallas más pequeñas */
  @media (max-width: 600px) {
    .container {
      flex-direction: column;
      /* Cambia la dirección del flex a columna */
    }

    .imatge {
      flex: 1 1 100%;
      /* La primera columna ocupa el 100% del ancho en dispositivos pequeños */
    }
  }
</style>