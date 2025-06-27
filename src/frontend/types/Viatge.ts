export interface Viatge {
  slug: string;
  viatge: string;
  descripcio: string;
  pais_cat: string;
  dataInici: string;
  dataFi?: string;
  id: number;
}

export interface VisitaEspai extends Viatge {
  nom: string;
  any1: string;
}
