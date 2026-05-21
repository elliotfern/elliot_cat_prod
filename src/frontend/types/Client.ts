export interface Client {
  id: string; // PK (INT)
  clientNom: string; // VARCHAR
  clientCognoms: string | null; // VARCHAR
  clientEmail: string | null; // VARCHAR
  clientWeb: string | null; // VARCHAR (URL)
  clientNIF: string | null; // VARCHAR
  clientEmpresa: string | null; // VARCHAR
  clientAdreca: string | null; // VARCHAR
  clientCP: string | null; // VARCHAR

  pais_id: string | null; // UUID v7 en texto (BINARY(16) en BD)
  provincia_id: string | null; // UUID v7 en texto
  ciutat_id: string | null; // UUID v7 en texto
  estat_id: string | null; // UUID v7 en texto

  ciutat_ca: string;
  pais_ca: string;
  provincia_ca: string;
  ciutat_final: string;

  clientTelefon: string | null; // VARCHAR
  clientStatus: number; // INT (p.ej. 0/1/2)
  clientRegistre: string | null; // 'YYYY-MM-DD' (DATE)
  estat: string;
  ordre: number;
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
