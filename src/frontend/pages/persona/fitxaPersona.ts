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

    // ==========================
    // FECHAS SISTEMA (IMPORTANTE)
    // ==========================
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

    // ==========================
    // EXTRA INFO
    // ==========================
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
  ['Enginyer/a informàtic', renderHistoriador],
  ['Actor/a', renderActor],
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
    // FECHAS DE CREACIÓN / MODIFICACIÓN
    // ==========================
    const dateElement = document.getElementById('dateCreated');
    const dateElement2 = document.getElementById('dateModified');

    if (dateElement && persona.dateCreated) {
      const d = new Date(persona.dateCreated);
      dateElement.textContent = `${d.getDate()}/${d.getMonth() + 1}/${d.getFullYear()}`;
    }

    if (dateElement2 && persona.dateModified) {
      const isSame = persona.dateModified === persona.dateCreated || persona.dateModified === '0000-00-00';

      if (!isSame) {
        const d = new Date(persona.dateModified);
        dateElement2.innerHTML = `| <strong>Darrera modificació:</strong> ${d.getDate()}/${d.getMonth() + 1}/${d.getFullYear()}`;
      } else {
        dateElement2.textContent = '';
      }
    }

    // ==========================
    // NACIMIENTO / DEFUNCIÓN
    // ==========================
    const mesosCatala = ['gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];

    const anyNaixement = persona.anyNaixement ?? 0;
    const diaNaixement = persona.diaNaixement ?? 0;
    const mesNaixement = persona.mesNaixement ?? 0;

    const anyDefuncio = persona.anyDefuncio ?? 0;
    const diaDefuncio = persona.diaDefuncio ?? 0;
    const mesDefuncio = persona.mesDefuncio ?? 0;

    const diaN = diaNaixement || '';
    const mesN = mesNaixement || '';

    let dataNaixement = anyNaixement.toString();
    if (diaN && mesN) {
      dataNaixement = `${diaN} ${mesosCatala[mesN - 1]} ${anyNaixement}`;
    }

    const diaD = diaDefuncio || '';
    const mesD = mesDefuncio || '';

    let dataDefuncio = '';
    if (anyDefuncio) {
      dataDefuncio = anyDefuncio.toString();
      if (diaD && mesD) {
        dataDefuncio = `${diaD} ${mesosCatala[mesD - 1]} ${anyDefuncio}`;
      }
    }

    // ==========================
    // EDAD
    // ==========================
    let edad = 0;

    if (anyNaixement) {
      // Persona fallecida
      if (anyDefuncio) {
        edad = anyDefuncio - anyNaixement;

        // Ajuste si murió antes de cumplir años
        if (mesDefuncio < mesNaixement || (mesDefuncio === mesNaixement && diaDefuncio < diaNaixement)) {
          edad--;
        }
      } else {
        // Persona viva
        const now = new Date();

        edad = now.getFullYear() - anyNaixement;

        // Ajuste si aún no ha cumplido años este año
        if (now.getMonth() + 1 < mesNaixement || (now.getMonth() + 1 === mesNaixement && now.getDate() < diaNaixement)) {
          edad--;
        }
      }
    }

    // ==========================
    // CIUDADES
    // ==========================
    const ciutatNaixement = persona.ciutatNaixement ? ` (${persona.ciutatNaixement})` : '';

    const ciutatDefuncio = persona.ciutatDefuncio ? ` (${persona.ciutatDefuncio})` : '';

    // ==========================
    // DETALLES
    // ==========================
    const quadreDetalls = document.querySelector('.quadre-detalls') as HTMLElement;
    if (quadreDetalls) quadreDetalls.innerHTML = '';

    const parrafosHTML: { label: string; value: string }[] = [];

    parrafosHTML.push({
      label: 'Naixement: ',
      value: `${dataNaixement} ${ciutatNaixement} ${!anyDefuncio ? `- ${edad} anys` : ''}`,
    });

    if (anyDefuncio) {
      parrafosHTML.push({
        label: 'Defunció: ',
        value: `${dataDefuncio} ${ciutatDefuncio} - ${edad} anys`,
      });
    }

    parrafosHTML.push({ label: 'Sexe: ', value: persona.sexe }, { label: 'Professió / grup: ', value: persona.grupsText }, { label: 'País/Nacionalitat: ', value: persona.paisAutor }, { label: 'Pàgina Viquipèdia: ', value: `<a href="${persona.web}" target="_blank">Enllaç extern</a>` }, { label: 'Biografia: ', value: persona.descripcio || 'No disponible' });

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
    // PROFESIONES
    // ==========================
    const quadreProfessio = document.querySelector('.quadre-professio') as HTMLElement;
    if (!quadreProfessio) return;

    quadreProfessio.innerHTML = '';

    // normalizar grupos
    const grups = Array.from(
      new Set(
        persona.grupsText
          .split(',')
          .map((g) => g.trim())
          .filter(Boolean)
      )
    );

    // 🔥 clave: evitar render duplicado por función, no por grupo
    const rendered = new Set<Function>();

    for (const grup of grups) {
      const renderer = professionRenderers.get(grup);

      if (!renderer) continue;

      // 🔥 dedupe por función real
      if (rendered.has(renderer)) continue;

      rendered.add(renderer);

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

async function renderActor(persona: PersonaView) {
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
