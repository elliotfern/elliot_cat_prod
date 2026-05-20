export type Pressupost = {
  id: string;
  concepte: string;

  client_id: string;
  servei_id: string;
  estat_id: string;

  import: number;
  data: string;

  created_at: string;
  modified_at: string;

  // joins del backend
  idClient: string;
  clientNom: string;
  clientCognoms?: string;
  clientEmail?: string;
  clientEmpresa?: string;

  estat: string;
  producte?: string;

  any?: number;
};
