import { Ciutat } from '../Entity/Ciutat';

export interface CiutatRepository {
  getById(id: string): Promise<Ciutat>;
  getAll(): Promise<Ciutat[]>;
  create(data: Omit<Ciutat, 'id'>): Promise<Ciutat>;
  update(id: string, data: Omit<Ciutat, 'id'>): Promise<Ciutat>;
  delete(id: string): Promise<void>;
}
