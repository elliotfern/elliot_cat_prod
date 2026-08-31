import { Ciutat } from '../../../Domain/Ciutat/Entity/Ciutat';
import { CiutatRepository } from '../../../Domain/Ciutat/Repository/CiutatRepository';

export class GetCiutats {
  constructor(private readonly repository: CiutatRepository) {}

  async execute(): Promise<Ciutat[]> {
    return this.repository.getAll();
  }
}
