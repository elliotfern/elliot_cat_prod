<script type="module">
  authorsTableLibrary();
</script>

<div id="barraNavegacioContenidor"></div>

<h1>Arts escèniques, cinema i televisió: llistat d'actors/es</h1>

<?php if ($viewModel->isAdmin) : ?>
  <p>
    <button onclick="window.location.href='<?php echo $url['persona']; ?>/nova-persona/'" class="button btn-gran btn-secondari">Crea nou actor/a</button>
  </p>
<?php endif; ?>

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
      .then(response => {

        // 🔥 el array real está aquí
        const data = response.data;

        let html = '';

        data.forEach(author => {

          html += `
            <tr>

              <td class="text-center">
                <a 
                  id="${author.id}"
                  title="Author page"
                  href="https://${window.location.host}/gestio/base-dades-persones/fitxa-persona/${author.slug}"
                >
                  <img 
                    src="https://media.elliot.cat/img/persona/${author.nameImg}.jpg"
                    style="height:70px"
                  >
                </a>
              </td>

              <td>
                <a 
                  id="${author.id}"
                  title="Author page"
                  href="https://${window.location.host}/gestio/base-dades-persones/fitxa-persona/${author.slug}"
                >
                  ${author.nom} ${author.cognoms}
                </a>
              </td>

              <td>
                ${
                  !author.any_defuncio
                    ? author.any_naixement
                    : `${author.any_naixement} - ${author.any_defuncio}`
                }
              </td>

              <td>${author.pais_ca ?? ''}</td>

              <td>
                <a href="https://${window.location.host}/gestio/base-dades-persones/modifica-persona/${author.slug}">
                  <button type="button" class="btn btn-sm btn-warning">
                    Modifica
                  </button>
                </a>
              </td>

              <td>
                <button type="button" class="btn btn-sm btn-danger">
                  Elimina
                </button>
              </td>

            </tr>
          `;
        });

        const tableBody = document.querySelector('#actorsTable tbody');

        if (tableBody) {
          tableBody.innerHTML = html;
        } else {
          console.error('No se encontró el tbody.');
        }

      })
      .catch(error => {
        console.error('Error en la petición:', error);
      });
  }
</script>