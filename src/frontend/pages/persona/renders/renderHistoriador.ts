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

export async function renderHistoriador(persona: PersonaView) {
  const res = await fetch(`/api/biblioteca/get/autorLlibres?id=${persona.id}`);

  if (!res.ok) return null;

  const json = await res.json();

  if (!json.success || !Array.isArray(json.data)) return null;

  const llibres = json.data;

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
              (l: any) => `
            <tr>
              <td>
                <a href="https://elliot.cat/gestio/biblioteca/fitxa-llibre/${l.slug}">
                  ${l.titol}
                </a>
              </td>
              <td>${l.any ?? ''}</td>
              <td class="text-end">
                <a href="https://elliot.cat/gestio/biblioteca/modifica-llibre/${l.slug}"
                   class="btn btn-sm btn-outline-warning">
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
