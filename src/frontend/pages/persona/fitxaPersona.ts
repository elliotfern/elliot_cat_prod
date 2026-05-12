// Respuesta genérica de la API
interface ApiResponse<T> {
  status: 'success' | 'error';
  message: string;
  errors: any[];
  data: T;
}

interface PersonaApiData {
  id: string;
  cognoms: string;
  nom: string;

  pais_ca: string | null;
  idPais: number | null;

  any_naixement: number | null;
  any_defuncio: number | null;

  nameImg: string | null;
  alt: string | null;

  web: string | null;
  created_at: string | null;
  updated_at: string | null;
  descripcio: string | null;

  slug: string;
  idImg: number | null;

  mes_naixement: number | null;
  dia_naixement: number | null;
  mes_defuncio: number | null;
  dia_defuncio: number | null;

  ciutatNaixement: string | null;
  ciutatDefuncio: string | null;
  idCiutatNaixement: number | null;
  idCiutatDefuncio: number | null;

  grups: { id: string; nom: string }[];
  sexe_id: number | null;
}

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

// ==========================
// MAPPING
// ==========================

function mapPersona(api: PersonaApiData): PersonaView {
  return {
    id: api.id,
    slug: api.slug,

    nom: api.nom ?? '',
    cognoms: api.cognoms ?? '',

    img: api.nameImg ?? '',
    alt: api.alt ?? '',

    web: api.web ?? '',
    descripcio: api.descripcio ?? '',

    dateCreated: api.created_at ?? null,
    dateModified: api.updated_at ?? null,

    anyNaixement: api.any_naixement ?? null,
    anyDefuncio: api.any_defuncio ?? null,

    mesNaixement: api.mes_naixement ?? null,
    diaNaixement: api.dia_naixement ?? null,

    mesDefuncio: api.mes_defuncio ?? null,
    diaDefuncio: api.dia_defuncio ?? null,

    ciutatNaixement: api.ciutatNaixement ?? null,
    ciutatDefuncio: api.ciutatDefuncio ?? null,

    paisAutor: api.pais_ca ?? '',

    sexe: api.sexe_id === 1 ? 'Home' : api.sexe_id === 2 ? 'Dona' : '',

    grupsText: Array.isArray(api.grups) ? api.grups.map((g) => g.nom).join(', ') : '',
  };
}

// ==========================
// RENDERERS MAP
// ==========================

const professionRenderers = new Map<string, (p: PersonaView) => Promise<HTMLElement | string | null>>([
  ['Historiador/a', renderHistoriador],
  ['Escriptor/a', renderHistoriador],
  ['Politòleg/a', renderHistoriador],
  ['Filòsof/a', renderHistoriador],
  ['Sociòleg/a', renderHistoriador],
  ['Periodista', renderHistoriador],
  ['Economista', renderHistoriador],
]);

// ==========================
// MAIN FUNCTION
// ==========================

export async function fitxaPersona(url: string, id: string, tipus: string, callback: (persona: PersonaApiData) => void) {
  try {
    const res = await fetch(`${url}${id}`);
    if (!res.ok) throw new Error('Error AJAX');

    const json: ApiResponse<PersonaApiData> = await res.json();
    callback(json.data);

    const persona = mapPersona(json.data);

    // ==========================
    // HEADER
    // ==========================
    const imgElement = document.getElementById('nameImg') as HTMLImageElement;
    const altElement = document.getElementById('alt');

    if (imgElement && altElement) {
      imgElement.src = `https://media.elliot.cat/img/persona/${persona.img}.jpg`;
      altElement.innerHTML = persona.alt;
    }

    const nomElement = document.getElementById('nom');
    if (nomElement) {
      nomElement.innerHTML = `${persona.nom} ${persona.cognoms}`;
    }

    // ==========================
    // DETALLES
    // ==========================
    const quadreDetalls = document.querySelector('.quadre-detalls') as HTMLElement;
    if (quadreDetalls) quadreDetalls.innerHTML = '';

    const parrafosHTML: { label: string; value: string }[] = [];

    parrafosHTML.push({
      label: 'Sexe: ',
      value: persona.sexe,
    });

    parrafosHTML.push({
      label: 'Professió / grup: ',
      value: persona.grupsText,
    });

    parrafosHTML.push({
      label: 'Pais/Nacionalitat: ',
      value: persona.paisAutor,
    });

    parrafosHTML.push({
      label: 'Pàgina Viquipèdia: ',
      value: `<a href="${persona.web}" target="_blank">Enllaç extern</a>`,
    });

    parrafosHTML.push({
      label: 'Biografia: ',
      value: persona.descripcio || 'No disponible',
    });

    parrafosHTML.forEach((item) => {
      const p = document.createElement('p');
      const strong = document.createElement('strong');
      strong.textContent = item.label;

      const span = document.createElement('span');
      span.innerHTML = item.value;

      p.appendChild(strong);
      p.appendChild(span);

      quadreDetalls.appendChild(p);
    });

    // ==========================
    // PROFESIÓN BLOCKS (FIX DUPLICADOS)
    // ==========================
    const quadreProfessio = document.querySelector('.quadre-professio') as HTMLElement;
    if (!quadreProfessio) return;

    quadreProfessio.innerHTML = '';

    const grups = Array.from(
      new Set(
        persona.grupsText
          .split(',')
          .map((g) => g.trim())
          .filter(Boolean)
      )
    );

    const rendered = new Set<string>(); // 🔥 evita duplicados por renderer

    for (const grup of grups) {
      const renderer = professionRenderers.get(grup);

      if (!renderer) continue;

      // si ya renderizó ese tipo → skip
      if (rendered.has(renderer.name)) continue;

      rendered.add(renderer.name);

      const content = await renderer(persona);

      if (!content) continue;

      if (typeof content === 'string') {
        const div = document.createElement('div');
        div.innerHTML = content;
        quadreProfessio.appendChild(div);
      } else {
        quadreProfessio.appendChild(content);
      }
    }
  } catch (err) {
    console.error('Error fitxaPersona:', err);
  }
}

// ==========================
// HISTORIADOR (ORIGINAL RESTAURADO)
// ==========================

async function renderHistoriador(persona: PersonaView) {
  const res = await fetch(`/api/biblioteca/get/autorLlibres?id=${persona.id}`);

  if (!res.ok) return null;

  const json = await res.json();

  if (json.status !== 'success' || !Array.isArray(json.data)) return null;

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
