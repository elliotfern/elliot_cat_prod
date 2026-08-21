export interface Client {
  id: string;
  nom: string;
  cognoms: string | null;
  email: string | null;
  web: string | null;
  nif: string | null;
  empresa: string | null;
  adreca: string | null;
  cp: string | null;
  ciutat_id: string | null;
  provincia_id: string | null;
  pais_id: string | null;
  estat_id: string | null;
  telefon: string | null;
  registre: string | null;
  num: number;
  estat: string;
}

export interface FacturaClient {
  id: string;
  numero_factura: string;
  concepte: string | null;

  data_factura: string;
  data_venciment: string | null;

  base_imposable: number;
  import_iva: number;
  total_factura: number;

  any: string;

  estat: string | null;
  tipusNom: string | null;
  ivaPercen: number | null;
}

export interface PressupostClient {
  id: string;
  concepte: string | null;
  client_id: string;
  servei_id: string;
  estat_id: string;
  import: number;
  data: string;
  created_at: string;
  modified_at: string;

  estatNom: string | null;
  producte: string | null;
  any: number;
}
