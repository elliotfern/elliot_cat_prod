export interface Factura {
  id: number;
  yearInvoice: number;
  clientEmpresa?: string;
  clientNom?: string;
  clientCognoms?: string;
  facData: string; // o Date, si luego parseas
  facConcepte: string;
  facTotal: number;
  estat: string;
  slug?: string; // por si luego lo usas como en otras vistas
  any: string;
}
