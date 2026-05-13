<?php
$slug = $routeParams[0];
?>

<div id="barraNavegacioContenidor"></div>

<div class="container">

  <h1>Arts escèniques, cinema i televisió: llistat pel·lícules</h1>

  <div id='fitxaSerie'></div>

  <hr>

  <div id='taulaActors'></div>

</div>


<script>
  actorsDeLaSerie("<?php echo $slug; ?>");

  // =========================================
  // ACTORS DE LA SÈRIE
  // =========================================

  async function actorsDeLaSerie(id) {

    const urlAjax = `/api/cinema/get/actors-serie?serie=${id}`;

    const container = document.getElementById("actors-container");

    try {

      const response = await fetch(urlAjax, {
        method: "GET",
      });

      if (!response.ok) {
        throw new Error(`Error en la petición: ${response.statusText}`);
      }

      const json = await response.json();

      // 🔥 NUEVO FORMATO API
      const actors = json.data ?? [];

      if (actors.length > 0) {

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

              ${actors.map(actor => `

                <tr>

                  <td>
                    <a
                      id="actor-${actor.idActor}"
                      title="Actor"
                      href="${window.location.origin}/gestio/base-dades-persones/fitxa-persona/${actor.slug}"
                    >

                      <img
                        src="https://media.elliot.cat/img/persona/${actor.nameImg}.jpg"
                        width="100"
                        height="auto"
                      >

                    </a>
                  </td>

                  <td>
                    <a
                      id="actor-${actor.idActor}"
                      title="Actor"
                      href="${window.location.origin}/gestio/base-dades-persones/fitxa-persona/${actor.slug}"
                    >

                      ${actor.nom} ${actor.cognoms}

                    </a>
                  </td>

                  <td>${actor.role ?? ''}</td>

                  <td>
                    <a
                      href="${window.location.origin}/gestio/cinema/modifica-actor-serie/${actor.idCast}"
                      class="btn btn-secondary btn-sm modificar-link"
                    >
                      Modificar
                    </a>
                  </td>

                  <td>
                    <button type="button" class="btn btn-danger btn-sm">
                      Elimina
                    </button>
                  </td>

                </tr>

              `).join("")}

            </tbody>

          </table>
        `;

        container.innerHTML = tableHTML;

      } else {

        container.innerHTML = `
          <p class="text-muted">
            No hi ha actors assignats a aquesta sèrie.
          </p>
        `;
      }

    } catch (error) {

      console.error("Error al obtener los actores:", error);
    }
  }
</script>