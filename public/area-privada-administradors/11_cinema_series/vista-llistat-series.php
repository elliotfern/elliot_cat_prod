  <div id="barraNavegacioContenidor"></div>

  <div class="container">

    <h1>Arts escèniques, cinema i televisió: llistat sères tv</h1>

    <p>
      <button onclick="window.location.href='<?php echo APP_INTRANET . $url['cinema']; ?>/nova-serie/'" class="button btn-gran btn-secondari">Afegir sèrie tv</button>
    </p>


    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle" id="seriesTable">
        <thead class="table-dark">
          <tr>
            <th>Nom</th>
            <th>Gènere</th>
            <th>Director/a</th>
            <th>Any</th>
            <th>País</th>
            <th>Idioma</th>
            <th class="text-end">Accions</th>
          </tr>
        </thead>
        <tbody id="seriesContainer">
        </tbody>
      </table>
    </div>

  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      obtenirPelicules();
    });

    function obtenirPelicules() {
      let urlAjax = "/api/cinema/get/series";

      fetch(urlAjax, {
          method: "GET",
        })
        .then((response) => response.json())
        .then((response) => {
          try {
            const data = response.data;

            let rows = "";

            data.forEach((serie) => {
              const slug = serie.slug ?? "#";
              const slugDirector = serie.slugDirector ?? "#";

              rows += `
            <tr>

              <td>
                <a href="${window.location.origin}/gestio/cinema/fitxa-serie/${slug}">
                  ${serie.name ?? ''}
                </a>
              </td>

              <td>
                <span class="badge bg-dark">
                  ${serie.genere ?? ''}
                </span>
              </td>

              <td>
                <a href="${window.location.origin}/gestio/base-dades-persones/fitxa-persona/${slugDirector}">
                  ${serie.nom ?? ''} ${serie.cognoms ?? ''}
                </a>
              </td>

              <td>${serie.startYear ?? ''}</td>
              <td>${serie.country ?? ''}</td>
              <td>${serie.lang ?? ''}</td>

              <td class="text-end">
                <button
                  onclick="window.location.href='${window.location.origin}/gestio/cinema/modifica-serie/${slug}'"
                  class="button btn-petit"
                >
                  Modificar
                </button>
              </td>

            </tr>
          `;
            });

            document.getElementById("seriesContainer").innerHTML = rows;

          } catch (error) {
            console.error("Error al parsear JSON:", error);
          }
        })
        .catch((error) => console.error("Error en la petición:", error));
    }
  </script>