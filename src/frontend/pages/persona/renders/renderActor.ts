import { api } from '../../../Infrastructure/Api/Client/ApiClient';
import { getIsAdmin } from '../../../services/auth/isAdmin';
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

export async function renderActor(persona: PersonaView): Promise<HTMLElement | null> {
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

    // Pel·lícules
    if (peliculesRes.status === 'fulfilled') {
      pelicules = peliculesRes.value;
    } else {
      console.error('Error carregant pel·lícules', peliculesRes.reason);
    }

    // Sèries
    if (seriesRes.status === 'fulfilled') {
      series = seriesRes.value;
    } else {
      console.error('Error carregant sèries', seriesRes.reason);
    }
  } catch (error) {
    console.error(error);
    return null;
  }

  if (!pelicules.length && !series.length) {
    return null;
  }

  const isAdmin = await getIsAdmin();
  const wrapper = document.createElement('div');

  // ==========================
  // TAULA PEL·LÍCULES
  // ==========================

  if (pelicules.length) {
    const section = document.createElement('div');

    section.innerHTML = `
      <h4 class="mb-3 mt-4">🎬 Pel·lícules</h4>

      <div class="table-responsive mb-5">
        <table class="table table-sm table-striped table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>Títol</th>
              <th>Rol</th>
              <th>Any</th>
              ${isAdmin ? '<th class="text-end">Accions</th>' : ''}
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    `;

    const tbody = section.querySelector('tbody');

    if (tbody) {
      for (const pelicula of pelicules) {
        const tr = document.createElement('tr');

        const tdTitol = document.createElement('td');
        const linkPelicula = document.createElement('a');

        linkPelicula.href = isAdmin ? `/gestio/cinema/fitxa-pelicula/${encodeURIComponent(pelicula.slug)}` : `/banc-dades/cinema/fitxa-pelicula/${encodeURIComponent(pelicula.slug)}`;

        linkPelicula.textContent = pelicula.titol;
        tdTitol.appendChild(linkPelicula);

        const tdRol = document.createElement('td');
        tdRol.textContent = pelicula.rol ?? '';

        const tdAny = document.createElement('td');
        tdAny.textContent = pelicula.anyInici != null ? String(pelicula.anyInici) : '';

        tr.append(tdTitol, tdRol, tdAny);

        if (isAdmin) {
          const tdAccions = document.createElement('td');
          tdAccions.className = 'text-end';

          const editLink = document.createElement('a');
          editLink.href = `/gestio/cinema/modifica-pelicula/${encodeURIComponent(pelicula.slug)}`;
          editLink.className = 'btn btn-sm btn-outline-warning';
          editLink.textContent = 'Modifica';

          tdAccions.appendChild(editLink);
          tr.appendChild(tdAccions);
        }

        tbody.appendChild(tr);
      }
    }

    wrapper.appendChild(section);
  }

  // ==========================
  // TAULA SÈRIES TV
  // ==========================

  if (series.length) {
    const section = document.createElement('div');

    section.innerHTML = `
      <h4 class="mb-3">📺 Sèries TV</h4>

      <div class="table-responsive">
        <table class="table table-sm table-striped table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>Títol</th>
              <th>Rol</th>
              <th>Anys</th>
              ${isAdmin ? '<th class="text-end">Accions</th>' : ''}
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    `;

    const tbody = section.querySelector('tbody');

    if (tbody) {
      for (const serie of series) {
        const tr = document.createElement('tr');

        const tdTitol = document.createElement('td');
        const linkSerie = document.createElement('a');

        linkSerie.href = isAdmin ? `/gestio/cinema/fitxa-serie/${encodeURIComponent(serie.slug)}` : `/banc-dades/cinema/fitxa-serie/${encodeURIComponent(serie.slug)}`;

        linkSerie.textContent = serie.titol;
        tdTitol.appendChild(linkSerie);

        const tdRol = document.createElement('td');
        tdRol.textContent = serie.role ?? '';

        const tdAnys = document.createElement('td');

        const anys = serie.anyFi != null ? `${serie.anyInici ?? ''} - ${serie.anyFi}` : serie.anyInici != null ? String(serie.anyInici) : '';

        tdAnys.textContent = anys;

        tr.append(tdTitol, tdRol, tdAnys);

        if (isAdmin) {
          const tdAccions = document.createElement('td');
          tdAccions.className = 'text-end';

          const editLink = document.createElement('a');
          editLink.href = `/gestio/cinema/modifica-serie/${encodeURIComponent(serie.slug)}`;
          editLink.className = 'btn btn-sm btn-outline-warning';
          editLink.textContent = 'Modifica';

          tdAccions.appendChild(editLink);
          tr.appendChild(tdAccions);
        }

        tbody.appendChild(tr);
      }
    }

    wrapper.appendChild(section);
  }

  return wrapper;
}
