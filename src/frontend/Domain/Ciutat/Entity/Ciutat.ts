import { Pais } from '../../Pais/Entity/Pais';

export interface Ciutat {
  id: string;
  ciutat: string;
  pais_id: string;
  updated_at: string;
  created_at: string;
  pais: Pais;
}
