export interface Client {
  id: number; // PK (INT)

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

  ciutat_ca: string;
  pais_ca: string;
  provincia_ca: string;

  clientTelefon: string | null; // VARCHAR
  clientStatus: number; // INT (p.ej. 0/1/2)
  clientRegistre: string | null; // 'YYYY-MM-DD' (DATE)
  estatNom: string;
}
