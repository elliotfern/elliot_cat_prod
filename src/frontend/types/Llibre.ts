export interface Llibre {
  id: number;
  slug: string;
  AutNom: string;
  AutCognom1: string;
  slugAuthor: string;
  titol_original: string;
  titol_catala: string;
  any: string;
  sub_genere_cat: string;
  nomGenCat: string;

  nom_grup: string;
  // NUEVO: array de autores
  autors?: AutorData[];

  // Compat legacy (por si algún endpoint viejo todavía devuelve esto)
  id_autor?: string;
  autorSlug?: string;
  nom?: string | null;
  cognoms?: string | null;
  llibreSlug?: string;

  idGrup: string;

  dateCreated: string | null;
  dateModified: string | null;

  img_id: string;
  nameImg: string;

  nomTipus: string | null;
  editorial: string | null;
  idioma_ca: string | null;

  // ahora es UUID v7 (según dices)
  estat_id: string;
  nomEstat: string;
  lang: string;
  editorial_id: string;
  grup: string;
  tipus_id: string;

  sub_tema_id: string;
  tema: string;
}

type AutorData = {
  id: string;
  nom: string | null;
  cognoms: string | null;
  slug: string | null;
};
