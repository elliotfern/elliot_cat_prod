import { PaisRepository } from '../../../Domain/Pais/Repository/PaisRepository';

export class DeletePais {
  constructor(private readonly repository: PaisRepository) {}

  async execute(id: string): Promise<void> {
    return this.repository.delete(id);
  }
}
