  <div id="barraNavegacioContenidor"></div>

  <div class="container">

    <h1>Arts escèniques, cinema i televisió: llistat sères tv</h1>

    <p>
      <button onclick="window.location.href='<?php echo APP_INTRANET . $url['cinema']; ?>/nova-serie/'" class="button btn-gran btn-secondari">Afegir sèrie tv</button>
    </p>


    <div class="container-fluid">
      <div class="row gap-3 justify-content-center llibresContainer" id="seriesContainer">
        <!-- aqui es mostren les pelicules -->
      </div>
    </div>

  </div>

  <script>
    // Escuchar el evento de entrada en el campo de búsqueda

    document.addEventListener("DOMContentLoaded", function() {
      obtenirPelicules(); // Mostrar todas las películas al cargar
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

            let pelicules = "";

            data.forEach((pelicula) => {
              pelicules += `
          <div class="col-sm-4 col-md-4 card">
            <h6>
              <span style="background-color:black;color:white;padding:5px;">
                ${pelicula.genere ?? ''}
              </span>
            </h6>

            <h3 class="links-contactes" style="margin-top: 15px;">
              <a href="${window.location.origin}/gestio/cinema/fitxa-serie/${pelicula.slug}">
                ${pelicula.name}
              </a>
            </h3>

            <p class="links-contactes autor">
              <strong>Director/a:</strong>
              <a href="${window.location.origin}/gestio/base-dades-persones/fitxa-persona/${pelicula.slugDirector}">
                ${pelicula.nom ?? ''} ${pelicula.cognoms ?? ''}
              </a>
            </p>

            <p><strong>Any: </strong> ${pelicula.startYear ?? ''}</p>
            <p><strong>País: </strong> ${pelicula.country ?? ''}</p>
            <p><strong>Idioma original: </strong> ${pelicula.lang ?? ''}</p>

            <button onclick="window.location.href='${window.location.origin}/gestio/cinema/modifica-serie/${pelicula.slug}'" class="button btn-petit">
              Modificar
            </button>
          </div>
        `;
            });

            document.getElementById("seriesContainer").innerHTML = pelicules;

          } catch (error) {
            console.error("Error al parsear JSON:", error);
          }
        })
        .catch((error) => console.error("Error en la petición:", error));
    }
  </script>