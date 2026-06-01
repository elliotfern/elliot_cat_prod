<script type="module">
  authorsTableLibrary();
</script>

<div class="container">

  <div id="barraNavegacioContenidor"></div>

  <main>
    <div class="container contingut">

      <h1>Arts escèniques, cinema i televisió: llistat directors/es</h1>

      <div id="isAdminButton" style="display: none;">
        <?php if (isset($_COOKIE['user_id']) && $_COOKIE['user_id'] === '1') : ?>
          <p>
            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['persona']; ?>/nova-persona/'" class="button btn-gran btn-secondari">Afegir actor/a o director/a</button>
          </p>
        <?php endif; ?>
      </div>

      <div class="">
        <table class="table table-striped datatable" id="actorsTable">
          <thead class="table-primary">
            <tr>
              <th></th>
              <th>Nom complet</th>
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
    const urlAjax = `https://${window.location.host}/api/cinema/get/directors`;

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
              <a id="${author.id}" title="Author page" href="https://${window.location.host}/gestio/cinema/fitxa-director/${author.slug}">
                <img src="https://media.elliot.cat/img/cinema-director/${author.nameImg}.jpg" style="height:70px">
              </a>
            </td>
            <td>
              <a id="${author.id}" title="Author page" href="https://${window.location.host}/gestio/cinema/fitxa-director/${author.slug}">
                ${author.nomComplet}
              </a>
            </td>
            <td>${author.pais_cat}
              </a>
            </td>

            <td>
             ${!author.anyDefuncio ? author.anyNaixement : `${author.anyNaixement} - ${author.anyDefuncio}`}
            </td>
            <td>
              <a href="https://${window.location.host}/gestio/cinema/modifica-director/${author.slug}">
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