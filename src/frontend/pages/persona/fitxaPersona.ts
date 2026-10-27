import { mapPersona, mapPersonaToFitxa } from '../../components/mappers/persona';
import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { Persona } from '../../types/Persona';
import { PersonaView } from '../../types/PersonaView';
import { renderFitxa } from '../../utils/renderFitxa';

import { renderActor } from './renders/renderActor';
import { renderHistoriador } from './renders/renderHistoriador';

// -------------------------
// BLOQUES
// -------------------------
interface FitxaBlock {
  key: string;
  professions: string[];
  render: (persona: PersonaView) => Promise<HTMLElement | null>;
}

interface RunFitxaBlocksOptions {
  containerSelector: string;
  persona: PersonaView;
}

// -------------------------
// CONFIG BLOQUES
// -------------------------
const fitxaBlocks: FitxaBlock[] = [
  {
    key: 'actor',
    professions: ['Actor/a'],
    render: renderActor,
  },

  {
    key: 'historiador',
    professions: ['Historiador/a', 'Escriptor/a', 'Politòleg/a', 'Filòsof/a', 'Sociòleg/a', 'Periodista', 'Economista', 'Enginyer/a informàtic'],
    render: renderHistoriador,
  },
];

// -------------------------
// BLOQUES EXTRA
// -------------------------
export async function runFitxaBlocks(options: RunFitxaBlocksOptions) {
  const container = document.querySelector(options.containerSelector);

  if (!container) return;

  container.innerHTML = '';

  for (const block of fitxaBlocks) {
    if (!block.professions.some((p) => options.persona.grups.includes(p))) {
      continue;
    }

    const result = await block.render(options.persona);

    if (!result) continue;

    const el =
      typeof result === 'string'
        ? Object.assign(document.createElement('div'), {
            innerHTML: result,
          })
        : result;

    container.appendChild(el);
  }
}

// -------------------------
// MAIN
// -------------------------

function renderTitolBancDades(): void {
  const container = document.getElementById('titolPagina');

  if (!container) return;

  let titol = document.getElementById('titolBancDades');

  if (!titol) {
    titol = document.createElement('h1');
    titol.id = 'titolBancDades';
    titol.className = 'mb-4';
    titol.textContent = 'Banc de dades: dones i homes que han fet història';

    container.prepend(titol);
  }
}

export async function fitxaPersona(url: string, slug: string) {
  renderTitolBancDades();

  try {
    // 1. API
    const personaApi = await api.get<Persona>(url, {
      slug,
    });

    // 2. VIEW NORMALIZADA
    const persona = mapPersona(personaApi);

    // 3. FITXA UI
    const fitxa = mapPersonaToFitxa(persona);

    const isAdmin = await getIsAdmin();

    const fitxaOptions = {
      containerId: 'fitxaPersona',
      title: fitxa.title,
      image: fitxa.image,
      fields: fitxa.fields,
      description: fitxa.description,
      dateCreated: fitxa.dateCreated,
      dateModified: fitxa.dateModified,
      ...(isAdmin
        ? {
            editButton: {
              basePath: 'base-dades-persones',
              action: 'modifica-persona',
              id: persona.slug,
              label: 'Modifica persona',
            },
          }
        : {}),
    };

    renderFitxa(fitxaOptions);

    // 4. BLOQUES EXTRA
    await runFitxaBlocks({
      containerSelector: '.quadre-extra',
      persona,
    });
  } catch (error) {
    console.error(error);

    return;
  }
}
