export interface Viatge {
  slug: string;
  viatge: string;
  descripcio: string;
  pais_ca: string;
  dataInici: string;
  dataFi: string;
  id: number;
  nameImg: string;
  alt: string;
  dateCreated: string;
  dateModified: string;
}

export interface VisitaEspai extends Viatge {
  nom: string;
  any1: string;
  dataVisita: string;
}
