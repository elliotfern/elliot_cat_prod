export interface Ciutat {
  id: number;
  ciutat: string;
  ciutat_ca: string;
  ciutat_en: string;
  updated_at: string;
  created_at: string;
  pais: Pais;
  pais_id: string;
}

interface Pais {
  id: string;
  pais_ca: string;
  pais_en: string;
}
