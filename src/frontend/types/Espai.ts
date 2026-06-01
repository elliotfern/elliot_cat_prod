export interface Espai {
  slug: string;
  nom: string;
  dataVisita: string;
  ciutat: string;
  viatge: string;
  viatgeSlug: string;
  grup_ids?: string[];
  status: string;
  message: string;
  id: string;
  ciutat_id: string;
  descripcio: string;
  img_id: string;
  tipus_id: string;
  nameImg: string;
  alt: string;
  any_fundacio: string;
  tipus: string;
  web: string;
  dateCreated: string;
  dateModified: string;
  coordinades_latitud: string;
  coordinades_longitud: string;
}

export interface EspaiVisitat {
  [key: string]: unknown;
  grup_ids?: string[];
  status: string;
  message: string;
  id: string;
  viatge_id: string;
  espai_id: string;
}
