export interface PersonaView {
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
  grups: string[];
}
