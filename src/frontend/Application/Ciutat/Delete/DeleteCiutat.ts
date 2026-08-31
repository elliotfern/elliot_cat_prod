import { CiutatRepository } from '../../../Domain/Ciutat/Repository/CiutatRepository';

export class DeleteCiutat {
  constructor(private readonly repository: CiutatRepository) {}

  async execute(id: string): Promise<void> {
    return this.repository.delete(id);
  }
}
