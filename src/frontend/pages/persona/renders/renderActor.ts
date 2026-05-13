interface PersonaView {
  id: string;
  slug: string;

  nom: string;
  cognoms: string;

  img: string;
  alt: string;

  web: string;
  descripcio: string;

  dateCreated: string | null;
  dateModified: string | null;

  anyNaixement?: number | null;
  anyDefuncio?: number | null;

  mesNaixement?: number | null;
  diaNaixement?: number | null;

  mesDefuncio?: number | null;
  diaDefuncio?: number | null;

  ciutatNaixement: string | null;
  ciutatDefuncio: string | null;

  paisAutor: string;
  sexe: string;
  grupsText: string;
}

export async function renderActor(persona: PersonaView) {
  const [peliculesRes, seriesRes] = await Promise.all([fetch(`/api/cinema/get/actor-pelicules?actor=${persona.id}`), fetch(`/api/cinema/get/actor-series?actor=${persona.id}`)]);

  let pelicules: any[] = [];
  let series: any[] = [];

  // ==========================
  // PELÍCULAS
  // ==========================

  if (peliculesRes.ok) {
    const peliculesJson = await peliculesRes.json();

    if (peliculesJson.status === 'success' && Array.isArray(peliculesJson.data)) {
      pelicules = peliculesJson.data;
    }
  }

  // ==========================
  // SERIES TV
  // ==========================

  if (seriesRes.ok) {
    const seriesJson = await seriesRes.json();

    if (seriesJson.status === 'success' && Array.isArray(seriesJson.data)) {
      series = seriesJson.data;
    }
  }

  // ==========================
  // SI NO HAY NADA → NULL
  // ==========================

  if (!pelicules.length && !series.length) {
    return null;
  }

  const wrapper = document.createElement('div');

  let html = '';

  // ==========================
  // TABLA PELÍCULAS
  // ==========================

  if (pelicules.length) {
    html += `
      <h4 class="mb-3 mt-4">🎬 Pel·lícules</h4>

      <div class="table-responsive mb-5">
        <table class="table table-sm table-striped table-hover align-middle">

          <thead class="table-dark">
            <tr>
              <th>Títol</th>
              <th>Rol</th>
              <th>Any</th>
              <th class="text-end">Accions</th>
            </tr>
          </thead>

          <tbody>

            ${pelicules
              .map(
                (p: any) => `
              
              <tr>

                <td>
                  <a href="https://elliot.cat/gestio/cinema/fitxa-pelicula/${p.slug}">
                    ${p.titol}
                  </a>
                </td>

                <td>${p.rol ?? ''}</td>

                <td>${p.anyInici ?? ''}</td>

                <td class="text-end">
                  <a
                    href="https://elliot.cat/gestio/cinema/modifica-pelicula/${p.slug}"
                    class="btn btn-sm btn-outline-warning"
                  >
                    Modifica
                  </a>
                </td>

              </tr>

            `
              )
              .join('')}

          </tbody>

        </table>
      </div>
    `;
  }

  // ==========================
  // TABLA SERIES TV
  // ==========================

  if (series.length) {
    html += `
      <h4 class="mb-3">📺 Sèries TV</h4>

      <div class="table-responsive">
        <table class="table table-sm table-striped table-hover align-middle">

          <thead class="table-dark">
            <tr>
              <th>Títol</th>
              <th>Rol</th>
              <th>Anys</th>
              <th class="text-end">Accions</th>
            </tr>
          </thead>

          <tbody>

            ${series
              .map((s: any) => {
                const anys = s.anyFi ? `${s.anyInici} - ${s.anyFi}` : s.anyInici;

                return `
                <tr>

                  <td>
                    <a href="https://elliot.cat/gestio/cinema/fitxa-serie/${s.slug}">
                      ${s.titol}
                    </a>
                  </td>

                  <td>${s.role ?? ''}</td>

                  <td>${anys ?? ''}</td>

                  <td class="text-end">
                    <a
                      href="https://elliot.cat/gestio/cinema/modifica-serie/${s.slug}"
                      class="btn btn-sm btn-outline-warning"
                    >
                      Modifica
                    </a>
                  </td>

                </tr>
              `;
              })
              .join('')}

          </tbody>

        </table>
      </div>
    `;
  }

  wrapper.innerHTML = html;

  return wrapper;
}
