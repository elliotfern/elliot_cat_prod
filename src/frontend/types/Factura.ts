export interface Factura {
  [key: string]: unknown;
  id: number;
  numero_factura: string;
  emissor_id: number | null;
  client_id: number;
  concepte: string;
  data_factura: string;
  data_venciment: string;
  base_imposable: number;
  despeses_extra: number | null;
  total_factura: number;
  import_iva: number;
  tipus_iva: number;
  estat: number;
  metode_pagament: number;
  notes: string | null;
  projecte_id: number | null;
  arxiu_url: string | null;
  recurrent: boolean;
  frequencia: 'mensual' | 'trimestral' | 'anual' | null;
  productes?: ProducteFactura[];
}

export interface ProducteFactura {
  producte_id: number | null;
  descripcio: string;
  preu: number;
}
