import { Ciutat } from '../../../Domain/Ciutat/Entity/Ciutat';
import { CiutatRepository } from '../../../Domain/Ciutat/Repository/CiutatRepository';

export class CreateCiutat {
  constructor(private readonly repository: CiutatRepository) {}

  async execute(data: Omit<Ciutat, 'id'>): Promise<Ciutat> {
    return this.repository.create(data);
  }
}
