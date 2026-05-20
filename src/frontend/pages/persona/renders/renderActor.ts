import { api } from '../../../core/api/client';
import { PersonaView } from '../../../types/PersonaView';

type ActorPelicula = {
  slug: string;
  titol: string;
  rol?: string | null;
  anyInici?: number | null;
};

type ActorSerie = {
  slug: string;
  titol: string;
  role?: string | null;
  anyInici?: number | null;
  anyFi?: number | null;
};

export async function renderActor(persona: PersonaView) {
  let pelicules: ActorPelicula[] = [];
  let series: ActorSerie[] = [];

  try {
    const [peliculesRes, seriesRes] = await Promise.allSettled([
      api.get<ActorPelicula[]>('cinema/get/actor-pelicules', {
        actor: persona.id,
      }),

      api.get<ActorSerie[]>('cinema/get/actor-series', {
        actor: persona.id,
      }),
    ]);

    // Películas
    if (peliculesRes.status === 'fulfilled') {
      pelicules = peliculesRes.value;
    } else {
      console.error('Error carregant pel·lícules', peliculesRes.reason);
    }

    // Series
    if (seriesRes.status === 'fulfilled') {
      series = seriesRes.value;
    } else {
      console.error('Error carregant sèries', seriesRes.reason);
    }
  } catch (error) {
    console.error(error);
    return null;
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
                (p) => `
              
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
              .map((s) => {
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
