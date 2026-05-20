import { mapPersona, mapPersonaToFitxa } from '../../components/mappers/persona';
import { api } from '../../core/api/client';
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
export async function fitxaPersona(url: string, id: string) {
  try {
    // 1. API
    const personaApi = await api.get<Persona>(url, {
      id,
    });

    // 2. VIEW NORMALIZADA
    const persona = mapPersona(personaApi);

    // 3. FITXA UI
    const fitxa = mapPersonaToFitxa(persona);

    renderFitxa({
      containerId: 'fitxaPersona',

      title: fitxa.title,
      image: fitxa.image,
      fields: fitxa.fields,
      description: fitxa.description,
      dateCreated: fitxa.dateCreated,
      dateModified: fitxa.dateModified,

      editButton: {
        basePath: 'base-dades-persones',
        action: 'modifica-persona',
        id: persona.slug,
        label: 'Modifica persona',
      },
    });

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
