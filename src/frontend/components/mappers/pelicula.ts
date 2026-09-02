import { Pelicula } from '../../types/Pelicula';
import { formatData } from '../../utils/formataData';

function safeDate(date?: string | null): string {
  return date ? formatData(date) : '';
}

export function mapPeliculaToFitxa(api: Pelicula) {
  return {
    title: api.pelicula,

    image: api.nameImg
      ? {
          src: `https://media.elliot.cat/img/cinema-pelicula/${api.nameImg}.jpg`,
          alt: api.pelicula,
        }
      : undefined,

    fields: [
      {
        label: 'Títol original',
        value: api.pelicula ?? '',
      },
      {
        label: 'Títol en català',
        value: api.pelicula_ca ?? '',
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
        value: api.pais ?? '',
      },
      {
        label: 'Idioma original',
        value: api.idioma ?? '',
      },
      {
        label: "Any d'estrena",
        value: api.any ? String(api.any) : '',
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
