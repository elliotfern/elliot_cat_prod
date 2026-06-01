export interface Emissor {
  [key: string]: unknown;
  id: number;
  nom: string;
  nif: string;
  numero_iva?: string;
  pais_id: number;
  adreca?: string;
  telefon?: string;
  email?: string;
}
