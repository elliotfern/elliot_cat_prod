import { Ciutat } from '../../../Domain/Ciutat/Entity/Ciutat';
import { CiutatRepository } from '../../../Domain/Ciutat/Repository/CiutatRepository';

export class UpdateCiutat {
  constructor(private readonly repository: CiutatRepository) {}

  async execute(id: string, data: Omit<Ciutat, 'id'>): Promise<Ciutat> {
    return this.repository.update(id, data);
  }
}
