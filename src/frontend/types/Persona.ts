export interface Persona {
  slug: string;
  nom: string;
  cognoms: string;
  autor_nom_complet: string;
  pais_ca: string;
  dia_naixement: number;
  mes_naixement: number;
  any_naixement: number;
  dia_defuncio: number;
  mes_defuncio: number;
  any_defuncio: number;
  id: string;
  profession: string;
  nameImg: string;
  status: string;
  message: string;
  espai_cat: string;
  municipi: number;
  comarca: number;
  provincia: number;
  comunitat: number;
  estat: number;
  experiencia_id: number;
  institucio_localitzacio: number;

  // --- PERSONA / AUTOR (alineado con DB)
  sexe_id: number;
  pais_autor_id: number;
  img_id: number;

  ciutat_naixement_id: number;
  ciutat_defuncio_id: number;
  descripcio: string;
  ciutatNaixement: string;
  ciutatDefuncio: string;
  web: string;

  // --- relaciones
  grups: GrupDTO[];
  grup_ids?: string[];
  grup: string[];
}

export type GrupDTO = { id: string; nom: string };
