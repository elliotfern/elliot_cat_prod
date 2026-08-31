import { Ciutat } from '../../../Domain/Ciutat/Entity/Ciutat';
import { CiutatRepository } from '../../../Domain/Ciutat/Repository/CiutatRepository';

export class GetCiutat {
  constructor(private readonly repository: CiutatRepository) {}

  async execute(id: string): Promise<Ciutat> {
    return this.repository.getById(id);
  }
}
