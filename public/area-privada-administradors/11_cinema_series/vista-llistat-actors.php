<script type="module">
  authorsTableLibrary();
</script>

<div class="container">

  <div id="barraNavegacioContenidor"></div>

  <main>
    <div class="container contingut">
      <h1>Arts escèniques, cinema i televisió: llistat d'actors/es</h1>

      <div id="isAdminButton" style="display: none;">
        <?php if (isset($_COOKIE['user_id']) && $_COOKIE['user_id'] === '1') : ?>
          <p>
            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['persona']; ?>/nova-persona/'" class="button btn-gran btn-secondari">Crea nou actor/a</button>
          </p>
        <?php endif; ?>
      </div>


      <div class="table-responsive">
        <table class="table table-striped" id="actorsTable">
          <thead class="table-primary">
            <tr>
              <th></th>
              <th>Nom</th>
              <th>Anys</th>
              <th>Pais</th>
              <th></th>
              <th></th>
            </tr>
          </thead>
          <tbody> <!-- Agregado este tbody -->
          </tbody>
        </table>
      </div>

    </div>
  </main>
</div>

<script>
  function authorsTableLibrary() {
    const urlAjax = `https://${window.location.host}/api/cinema/get/actors`;

    fetch(urlAjax)
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        try {
          let html = '';
          data.forEach(author => {
            html += `<tr>
            <td class="text-center">
              <a id="${author.data.id}" title="Author page" href="https://${window.location.host}/gestio/cinema/fitxa-actor/${author.data.slug}">
                <img src="https://media.elliot.cat/img/cinema-actor/${author.nameImg}.jpg" style="height:70px">
              </a>
            </td>
            <td>
              <a id="${author.data.id}" title="Author page" href="https://${window.location.host}/gestio/cinema/fitxa-actor/${author.data.slug}">
                ${author.data.nom} ${author.data.cognoms}
              </a>
            </td>
            
            <td>${!author.data.anyDefuncio ? author.data.anyNaixement : `${author.data.anyNaixement} - ${author.data.anyDefuncio}`}</td>

            <td>${author.data.pais_ca}</td>
           
            <td>
              <a href="https://${window.location.host}/gestio/persona/modifica-persona/${author.data.slug}">
                <button type="button" class="btn btn-sm btn-warning">Modifica</button>
              </a>
            </td>
            <td>
              <button type="button" class="btn btn-sm btn-danger">Elimina</button>
            </td>
          </tr>`;
          });

          const tableBody = document.querySelector('#actorsTable tbody');
          if (tableBody) {
            tableBody.innerHTML = html;
          } else {
            console.error("No se encontró el tbody de la tabla de autores.");
          }
        } catch (error) {
          console.error('Error al procesar los datos:', error);
        }
      })
      .catch(error => console.error('Error en la petición:', error));
  }
</script>