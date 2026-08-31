import { Pais } from '../../../Domain/Pais/Entity/Pais';
import { PaisRepository } from '../../../Domain/Pais/Repository/PaisRepository';

export class UpdatePais {
  constructor(private readonly repository: PaisRepository) {}

  async execute(id: string, data: Omit<Pais, 'id'>): Promise<Pais> {
    return this.repository.update(id, data);
  }
}
