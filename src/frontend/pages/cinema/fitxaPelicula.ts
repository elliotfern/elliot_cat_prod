// ==========================
// TYPES
// ==========================

interface ApiResponse<T> {
  status: 'success' | 'error';
  message: string;
  errors: any[];
  data: T[];
}

interface PeliculaApiData {
  id: string;

  pelicula: string;
  pelicula_ca?: string | null;
  slug: string;
  any: string | null;
  descripcio: string | null;
  dateCreated: string | null;
  dateModified: string | null;
  nom: string | null;
  cognoms: string | null;
  idioma_ca: string | null;
  pais_ca: string | null;
  nameImg: string | null;
  genere: string | null;
  idDirector: string | null;
  slugDirector: string | null;
}

// ==========================
// MAIN
// ==========================

export async function fitxaPelicula(slug: string) {
  const container = document.getElementById('fitxaPeli');

  if (!container) {
    console.error('No existeix #fitxaPeli');
    return;
  }

  const urlAjax = `/api/cinema/get/pelicula?peliSlug=${slug}`;

  try {
    const response = await fetch(urlAjax);

    if (!response.ok) {
      throw new Error(`HTTP Error ${response.status}`);
    }

    const json: ApiResponse<PeliculaApiData> = await response.json();

    if (json.status !== 'success' || !Array.isArray(json.data) || json.data.length === 0) {
      container.innerHTML = `
        <div class="alert alert-warning">
          No s'ha trobat la pel·lícula.
        </div>
      `;

      return;
    }

    const pelicula = json.data[0];

    // ==========================
    // FORMAT DATES
    // ==========================

    const formatDate = (date: string | null) => {
      if (!date || date === '0000-00-00') return '';

      const d = new Date(date);

      return `${d.getDate()}/${d.getMonth() + 1}/${d.getFullYear()}`;
    };

    // ==========================
    // DIRECTOR LINK
    // ==========================

    const directorHTML = pelicula.slugDirector
      ? `
        <a 
          href="${window.location.origin}/gestio/cinema/fitxa-director/${pelicula.slugDirector}"
        >
          ${pelicula.nom ?? ''} ${pelicula.cognoms ?? ''}
        </a>
      `
      : `<span class="text-muted">No disponible</span>`;

    // ==========================
    // HTML
    // ==========================

    container.innerHTML = `
      <div class="container-fluid">

        <div class="row g-4">

          <!-- IMATGE -->
          <div class="col-12 col-md-4 text-center">

            <img
              src="https://media.elliot.cat/img/cinema-pelicula/${pelicula.nameImg ?? 'no-image'}.jpg"
              class="img-fluid rounded shadow-sm border"
              alt="${pelicula.pelicula}"
              title="${pelicula.pelicula}"
            >

          </div>

          <!-- DETALLS -->
          <div class="col-12 col-md-8">

            <div class="card shadow-sm">

              <div class="card-body">

                <h2 class="mb-4">
                  ${pelicula.pelicula}
                </h2>

                <div class="mb-2">
                  <strong>Títol en català:</strong>
                  ${pelicula.pelicula_ca ?? '-'}
                </div>

                <div class="mb-2">
                  <strong>Director/a:</strong>
                  ${directorHTML}
                </div>

                <div class="mb-2">
                  <strong>País:</strong>
                  ${pelicula.pais_ca ?? '-'}
                </div>

                <div class="mb-2">
                  <strong>Idioma original:</strong>
                  ${pelicula.idioma_ca ?? '-'}
                </div>

                <div class="mb-2">
                  <strong>Any d'estrena:</strong>
                  ${pelicula.any ?? '-'}
                </div>

                <div class="mb-2">
                  <strong>Gènere:</strong>
                  ${pelicula.genere ?? '-'}
                </div>

                <div class="mb-2">
                  <strong>Fitxa creada:</strong>
                  ${formatDate(pelicula.dateCreated)}
                </div>

                <div class="mb-2">
                  <strong>Darrera modificació:</strong>
                  ${formatDate(pelicula.dateModified)}
                </div>

              </div>

            </div>

          </div>

        </div>

        <!-- DESCRIPCIÓ -->
        <div class="card mt-4 shadow-sm">

          <div class="card-body">

            <h4 class="mb-3">
              Crítica de la pel·lícula
            </h4>

            <div>
              ${pelicula.descripcio && pelicula.descripcio.trim() !== '' ? pelicula.descripcio : '<span class="text-muted">Sense descripció</span>'}
            </div>

          </div>

        </div>

      </div>
    `;
  } catch (error) {
    console.error('Error carregant fitxa pel·lícula:', error);

    container.innerHTML = `
      <div class="alert alert-danger">
        Error carregant la fitxa de la pel·lícula.
      </div>
    `;
  }
}
