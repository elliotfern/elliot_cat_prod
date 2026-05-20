export interface Despesa {
  id?: number;
  data: string; // 'YYYY-MM-DD'
  data_pagament?: string | null;
  concepte: string;
  proveidor_id: number;
  receptor_id: number;
  base_imposable: number;
  tipus_iva: number;
  import_iva: number;
  total: number;
  metode_pagament?: 'transferencia' | 'targeta' | 'efectiu' | 'domicili';
  pagat?: number; // 0/1
  categoria_id: number;
  subcategoria_id?: number | null;
  tipus_despesa?: 'professional' | 'personal';
  client_id?: number | null;
  projecte_id?: number | null;
  arxiu_url?: string | null;
  deduible?: number; // 0/1
  recurrent?: number; // 0/1
  frequencia?: 'mensual' | 'trimestral' | 'anual' | null;
  notes?: string | null;
}
