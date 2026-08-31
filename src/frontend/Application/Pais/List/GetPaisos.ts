import { Pais } from '../../../Domain/Pais/Entity/Pais';
import { PaisRepository } from '../../../Domain/Pais/Repository/PaisRepository';

export class GetPaisos {
  constructor(private readonly repository: PaisRepository) {}

  async execute(): Promise<Pais[]> {
    return this.repository.getAll();
  }
}
