import { formatData } from '../../utils/formataData';

interface SerieApi {
  id: string;
  name: string;
  slug: string | undefined;
  startYear: string | undefined;
  endYear: string | undefined;
  season: string | undefined;
  chapter: string | undefined;
  director_id: string | undefined;
  lang: string | undefined;
  genere_id: string | undefined;
  pais_id: string | undefined;
  img_id: string | undefined;
  descripcio: string | undefined;

  idioma_ca: string | undefined;
  pais_ca: string | undefined;
  nameImg: string | undefined;
  genere: string | undefined;
  dateCreated?: string | undefined;
  dateModified?: string | undefined;
  nom: string | null;
  cognoms: string | null;
  slugDirector: string | null;
}

function safeDate(date?: string | null): string {
  return date ? formatData(date) : '';
}

export function mapSerieToFitxa(api: SerieApi) {
  return {
    title: api.name,

    image: api.nameImg
      ? {
          src: `https://media.elliot.cat/img/cinema-serie/${api.nameImg}.jpg`,
          alt: api.name,
        }
      : undefined,

    fields: [
      {
        label: 'Títol original',
        value: api.name ?? '',
      },
      {
        label: 'Director/a',
        value:
          api.nom && api.cognoms
            ? `<a href="/gestio/base-dades-persones/fitxa-persona/${api.slugDirector}">
                ${api.nom} ${api.cognoms}
               </a>`
            : '',
      },
      {
        label: 'País',
        value: api.pais_ca ?? '',
      },
      {
        label: 'Idioma original',
        value: api.idioma_ca ?? '',
      },
      {
        label: 'Anys emissió',
        value: api.endYear ? `${api.startYear} ${api.endYear}` : `${api.startYear}`,
      },
      {
        label: 'Número de temporades',
        value: api.season ?? '',
      },
      {
        label: 'Número de capítols',
        value: api.chapter ?? '',
      },
      {
        label: 'Gènere',
        value: api.genere ?? '',
      },
    ],

    description: api.descripcio ?? '',

    dateCreated: safeDate(api.dateCreated),
    dateModified: safeDate(api.dateModified),
  };
}
