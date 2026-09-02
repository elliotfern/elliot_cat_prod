export interface FacturaProveidor {
  id: string;
  proveidorNom: string;
  proveidor_id: string;
  nomCategoria: string;
  base_imposable: string;
  import_iva: string;
  total: string;
  pagat: string;
  data: string;
  concepte: string;

  created_at?: string;
  updated_at?: string;
}
