<div class="container">
  <div id="barraNavegacioContenidor"></div>

  <main>
    <div class="container contingut">

      <h1>Biblioteca</h1>
      <h2>Llistat de llibres</h2>

      <div id="isAdminButton" style="display: none;">
        <?php if (isUserAdmin()) :  ?>
          <p>
            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['biblioteca']; ?>/nou-llibre/'" class="button btn-gran btn-secondari">Afegir llibre</button>
          </p>
        <?php endif; ?>
      </div>

      <div id="taulaLlistatLlibres"></div>

    </div>
  </main>
</div>

<script>
  let urlAjax = devDirectory + "/api/biblioteca/get/?type=totsLlibres";


  fetch(urlAjax, {
      method: "GET",
      headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('token'),
        'Content-Type': 'application/json',
      }
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      if (!Array.isArray(data)) {
        console.error('Datos inesperados:', data);
        return;
      }

      let llibres = ''; // Inicializar variable
      data.forEach(llibre => {
        llibres += `
        <div class="col-sm-4 col-md-4 card">
          <h6><span style="background-color:black;color:white;padding:5px;">${llibre.codiGenere}.${llibre.nomGenCat}</span></h6>
          <h6><span style="background-color:black;color:white;padding:5px;margin-top:5px">${llibre.codiSubGenere}.${llibre.sub_genere_cat}</span></h6>
          <h3 class="links-contactes" style="margin-top: 15px;">
            <a href="${window.location.origin}/gestio/biblioteca/fitxa-llibre/${llibre.slug}" title="Fitxa del llibre">${llibre.titol}</a>
          </h3>
          <p class="links-contactes autor"><strong>Autor/a:</strong> <a href="${window.location.origin}/gestio/biblioteca/fitxa-autor/${llibre.slugAuthor}">${llibre.AutNom} ${llibre.AutCognom1}</a></p>
          <p><strong>Any: </strong> ${llibre.any}</p>
          <p><strong>Editorial: </strong> ${llibre.editorial}</p>
          <p><strong>Idioma original: </strong> ${llibre.idioma_ca}</p>
          <p><button type='button' class='button btn-petit'>${llibre.estat}</button></p>
          <p>
          <button onclick="window.location.href='${window.location.origin}/gestio/biblioteca/modifica-llibre/${llibre.slug}'" class="button btn-petit">Modificar</button>
          </p>
         
        </div>`;
      });

      document.getElementById('llibresContainer').innerHTML = llibres;
    })
    .catch(error => {
      console.error('Error en la solicitud:', error);
    });
</script>