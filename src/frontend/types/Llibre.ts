export interface Llibre {
  id: string;
  slug: string;
  AutNom: string;
  AutCognom1: string;
  slugAuthor: string;
  titol_original: string;
  titol_catala: string;
  any: string;
  sub_genere_cat: string;
  nomGenCat: string;
  grups?: { id: string; nom: string; slug: string }[];
  id_autor?: string;
  autorSlug?: string;
  nom: string;
  cognoms?: string | null;
  llibreSlug?: string;
  dateCreated: string | null;
  dateModified: string | null;
  img_id: string;
  nameImg: string;
  nomTipus: string | null;
  editorial: string | null;
  idioma_ca: string | null;
  estat_id: string;
  nomEstat: string;
  idioma_id: string;
  editorial_id: string;
  tipus_id: string;
  sub_tema_id: string;
  tema: string;
  descripcio: string | null;
  sub_tema: string;
  autors?: AutorData[];
}

type AutorData = {
  id: string;
  nom: string | null;
  cognoms: string | null;
  slug: string | null;
};
