import { api } from '../../../core/api/client';
import { PersonaView } from '../../../types/PersonaView';

type LlibreAutor = {
  slug: string;
  titol: string;
  any?: number | null;
};

export async function renderHistoriador(persona: PersonaView) {
  let llibres: LlibreAutor[];

  try {
    llibres = await api.get<LlibreAutor[]>('biblioteca/get/autorLlibres', {
      id: persona.id,
    });
  } catch (error) {
    console.error(error);
    return null;
  }

  if (!Array.isArray(llibres) || !llibres.length) {
    return null;
  }

  const wrapper = document.createElement('div');

  wrapper.innerHTML = `
    <h4 class="mb-3">📚 Llibres publicats</h4>

    <div class="table-responsive">
      <table class="table table-sm table-striped table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>Títol</th>
            <th>Any</th>
            <th class="text-end">Accions</th>
          </tr>
        </thead>

        <tbody>
          ${llibres
            .map(
              (l) => `
                <tr>
                  <td>
                    <a href="https://elliot.cat/gestio/biblioteca/fitxa-llibre/${l.slug}">
                      ${l.titol}
                    </a>
                  </td>

                  <td>${l.any ?? ''}</td>

                  <td class="text-end">
                    <a
                      href="https://elliot.cat/gestio/biblioteca/modifica-llibre/${l.slug}"
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

  return wrapper;
}
