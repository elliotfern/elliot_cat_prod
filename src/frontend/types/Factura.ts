export interface Factura {
  id: number;
  numero_factura: number;

  emissor_id?: number;
  client_id: number;

  clientEmpresa?: string;
  clientNom?: string;
  clientCognoms?: string;

  concepte: string;

  data_factura: string; // YYYY-MM-DD
  data_venciment: string; // YYYY-MM-DD

  base_imposable: number;
  despeses_extra?: number;

  total_factura: number;
  import_iva: number;

  tipus_iva: number;
  ivaPercen?: number;

  estat: number | string; // depende si usas label o id
  metode_pagament: number;

  yearInvoice: number;
  any: string;

  notes?: string;
  projecte_id?: number;
  arxiu_url?: string;

  recurrent?: number;
  frequencia?: 'mensual' | 'trimestral' | 'anual';

  slug?: string;
}
