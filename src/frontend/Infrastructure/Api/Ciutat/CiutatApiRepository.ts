import { apiSymfony } from '../Client/ApiClient';
import { API_URLS } from '../../../utils/apiUrls';
import { CiutatRepository } from '../../../Domain/Ciutat/Repository/CiutatRepository';
import { Ciutat } from '../../../Domain/Ciutat/Entity/Ciutat';

export class CiutatApiRepository implements CiutatRepository {
  async getById(id: string): Promise<Ciutat> {
    return apiSymfony.get<Ciutat>(API_URLS.GET.CIUTAT_ID(id));
  }

  async getAll(): Promise<Ciutat[]> {
    return apiSymfony.get<Ciutat[]>(API_URLS.GET.CIUTAT);
  }

  async create(data: Omit<Ciutat, 'id'>): Promise<Ciutat> {
    return apiSymfony.post<Ciutat>(API_URLS.POST.CIUTAT, data);
  }

  async update(id: string, data: Omit<Ciutat, 'id'>): Promise<Ciutat> {
    return apiSymfony.put<Ciutat>(API_URLS.PUT.CIUTAT(id), data);
  }

  async delete(id: string): Promise<void> {
    await apiSymfony.delete<void>(API_URLS.DELETE.CIUTAT(id));
  }
}
