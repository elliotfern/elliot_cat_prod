import { Pais } from '../Entity/Pais';

export interface PaisRepository {
  getById(id: string): Promise<Pais>;
  getAll(): Promise<Pais[]>;
  create(data: Omit<Pais, 'id'>): Promise<Pais>;
  update(id: string, data: Omit<Pais, 'id'>): Promise<Pais>;
  delete(id: string): Promise<void>;
}
