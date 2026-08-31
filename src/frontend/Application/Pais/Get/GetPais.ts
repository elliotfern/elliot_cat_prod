import { Pais } from '../../../Domain/Pais/Entity/Pais';
import { PaisRepository } from '../../../Domain/Pais/Repository/PaisRepository';

export class GetPais {
  constructor(private readonly repository: PaisRepository) {}

  async execute(id: string): Promise<Pais> {
    return this.repository.getById(id);
  }
}
