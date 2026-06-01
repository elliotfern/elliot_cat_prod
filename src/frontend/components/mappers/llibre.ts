import { Llibre } from '../../types/Llibre';
import { formatData } from '../../utils/formataData';

function safeDate(date?: string | null): string {
  return date ? formatData(date) : '';
}

function renderAutorsHtml(autors?: Llibre['autors']): string {
  if (!autors?.length) return '';

  return autors
    .map((a) => {
      const name = [a.nom, a.cognoms].filter(Boolean).join(' ');
      return `<a href="/gestio/base-dades-persones/fitxa-persona/${a.slug}">
                ${name || a.slug}
              </a>`;
    })
    .join(' / ');
}

export function mapLlibreToFitxa(api: Llibre) {
  return {
    title: api.titol_original,

    image: api.nameImg
      ? {
          src: `https://media.elliot.cat/img/biblioteca-llibre/${api.nameImg}.jpg`,
          alt: api.titol_original,
        }
      : undefined,

    fields: [
      {
        label: 'Títol original',
        value: api.titol_original ?? '',
      },
      {
        label: 'Títol en català',
        value: api.titol_catala ?? '',
      },
      {
        label: 'Autors',
        value: renderAutorsHtml(api.autors),
      },
      {
        label: 'Any',
        value: api.any ? String(api.any) : '',
      },
      {
        label: 'Editorial',
        value: api.editorial ?? '',
      },
      {
        label: 'Tipus',
        value: api.nomTipus ?? '',
      },
      {
        label: 'Idioma',
        value: api.idioma_ca ?? '',
      },
      {
        label: 'Gènere',
        value: api.tema ?? '',
      },
      {
        label: 'Subgènere',
        value: api.sub_tema ?? '',
      },
      {
        label: 'Estat',
        value: api.nomEstat ?? '',
      },
    ],

    description: api.descripcio ?? '',

    dateCreated: safeDate(api.dateCreated),
    dateModified: safeDate(api.dateModified),
  };
}
