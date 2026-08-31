import { Pais } from '../../../Domain/Pais/Entity/Pais';
import { PaisRepository } from '../../../Domain/Pais/Repository/PaisRepository';

export class CreatePais {
  constructor(private readonly repository: PaisRepository) {}

  async execute(data: Omit<Pais, 'id'>): Promise<Pais> {
    return this.repository.create(data);
  }
}
