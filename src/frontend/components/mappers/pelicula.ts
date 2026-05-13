import { formatData } from '../../utils/formataData';

interface PeliculaApi {
  id: string;
  pelicula: string;
  pelicula_ca: string | null;
  slug: string;

  any: string | number | null;

  descripcio: string | null;

  nameImg: string | null;

  pais_ca: string | null;
  idioma_ca: string | null;

  genere: string | null;

  dateCreated: string;
  dateModified: string;

  nom: string | null;
  cognoms: string | null;

  slugDirector: string | null;
}

export function mapPeliculaToFitxa(api: PeliculaApi) {
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
        value: api.pais_ca ?? '',
      },
      {
        label: 'Idioma original',
        value: api.idioma_ca ?? '',
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

    createdAt: api.dateCreated ? formatData(api.dateCreated) : undefined,
    updatedAt: api.dateModified ? formatData(api.dateModified) : undefined,
  };
}
