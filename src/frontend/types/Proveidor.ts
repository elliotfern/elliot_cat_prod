export interface Proveidor {
  id?: number;
  nom: string;
  nif?: string;
  adreca?: string;
  ciutat?: string;
  codi_postal?: string;
  pais?: string;
  telefon?: string;
  email?: string;
  web?: string;
  contacte?: string;
  notes?: string;
  contacte_id: string;

  client_id: string;
  concepte?: string;
  import?: string;
  data: string;

  cognoms?: string;
  empresa?: string;
  estat?: string;
  producte?: string;
  any?: string;
  num: string;

  created_at?: string;
  updated_at?: string;
}
