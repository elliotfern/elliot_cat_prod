import { formatData } from '../../utils/formataData';
import { PersonaView } from '../../types/PersonaView';

// -------------------------
// API TYPE
// -------------------------
export interface PersonaApi {
  id: string;
  nom: string;
  cognoms: string | null;
  slug: string;
  pais_ca: string | null;

  any_naixement: number | null;
  any_defuncio: number | null;

  ciutatNaixement: string | null;
  ciutatDefuncio: string | null;

  sexe_id: number | null;

  web: string | null;
  descripcio: string | null;

  nameImg: string | null;

  created_at?: string | null;
  updated_at?: string | null;
  grups: { id: string; nom: string }[];
}

// -------------------------
// SAFE DATE
// -------------------------
function safeDate(date?: string | null): string {
  return date ? formatData(date) : '';
}

function calcularEdad(persona: PersonaView): number | null {
  if (!persona.anyNaixement) return null;

  // Fallecida
  if (persona.anyDefuncio && persona.anyDefuncio > 0) {
    return persona.anyDefuncio - persona.anyNaixement;
  }

  // Viva
  const now = new Date();

  return now.getFullYear() - persona.anyNaixement;
}

// -------------------------
// 1. API → VIEW
// -------------------------
export function mapPersona(api: PersonaApi): PersonaView {
  return {
    id: api.id,
    slug: api.slug,

    nom: api.nom ?? '',
    cognoms: api.cognoms ?? '',

    img: api.nameImg ?? '',
    alt: `${api.nom ?? ''} ${api.cognoms ?? ''}`.trim(),

    web: api.web ?? '',
    descripcio: api.descripcio ?? '',

    dateCreated: api.created_at ?? null,
    dateModified: api.updated_at ?? null,

    anyNaixement: api.any_naixement ?? null,
    anyDefuncio: api.any_defuncio ?? null,

    ciutatNaixement: api.ciutatNaixement ?? null,
    ciutatDefuncio: api.ciutatDefuncio ?? null,

    paisAutor: api.pais_ca ?? '',
    sexe: api.sexe_id === 1 ? 'Home' : api.sexe_id === 2 ? 'Dona' : '',

    grupsText: '',
    grups: Array.isArray(api.grups) ? api.grups.map((g) => g.nom) : [],
  };
}

// -------------------------
// 2. VIEW → FITXA UI
// -------------------------
export function mapPersonaToFitxa(persona: PersonaView) {
  const fullName = `${persona.nom} ${persona.cognoms}`.trim();
  const edad = calcularEdad(persona);

  const naixement = persona.anyNaixement ? `${persona.anyNaixement}${persona.ciutatNaixement ? ` (${persona.ciutatNaixement})` : ''}${edad && !persona.anyDefuncio ? ` - ${edad} anys` : ''}` : '';

  const defuncio = persona.anyDefuncio && persona.anyDefuncio > 0 ? `${persona.anyDefuncio}${persona.ciutatDefuncio ? ` (${persona.ciutatDefuncio})` : ''}${edad ? ` - ${edad} anys` : ''}` : '';

  return {
    title: fullName,

    image: persona.img
      ? {
          src: `https://media.elliot.cat/img/persona/${persona.img}.jpg`,
          alt: fullName,
        }
      : undefined,

    fields: [
      { label: 'Nom complet', value: fullName },
      {
        label: 'Naixement',
        value: naixement,
      },

      {
        label: 'Defunció',
        value: defuncio,
      },
      { label: 'País', value: persona.paisAutor },
      { label: 'Ciutat naixement', value: persona.ciutatNaixement ?? '' },
      { label: 'Ciutat defunció', value: persona.ciutatDefuncio ?? '' },
      { label: 'Sexe', value: persona.sexe },
      {
        label: 'Web',
        value: persona.web ? `<a href="${persona.web}" target="_blank">Enllaç extern</a>` : '',
      },
    ],

    description: persona.descripcio ?? '',
    dateCreated: safeDate(persona.dateCreated),
    dateModified: safeDate(persona.dateModified),
  };
}
