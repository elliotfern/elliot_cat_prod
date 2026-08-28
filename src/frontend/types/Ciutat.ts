export interface Ciutat {
  id: number;
  ciutat: string;
  updated_at: string;
  created_at: string;
  pais: Pais;
  pais_id: string;
}

interface Pais {
  id: string;
  pais: string;
}
