import { apiSymfony } from '../Client/ApiClient';
import { API_URLS } from '../../../utils/apiUrls';

import { Pais } from '../../../Domain/Pais/Entity/Pais';
import { PaisRepository } from '../../../Domain/Pais/Repository/PaisRepository';

export class PaisApiRepository implements PaisRepository {
  async getById(id: string): Promise<Pais> {
    return apiSymfony.get<Pais>(API_URLS.GET.PAIS_ID(id));
  }

  async getAll(): Promise<Pais[]> {
    return apiSymfony.get<Pais[]>(API_URLS.GET.PAISOS);
  }

  async create(data: Omit<Pais, 'id'>): Promise<Pais> {
    return apiSymfony.post<Pais>(API_URLS.POST.PAIS, data);
  }

  async update(id: string, data: Omit<Pais, 'id'>): Promise<Pais> {
    return apiSymfony.put<Pais>(API_URLS.PUT.PAIS(id), data);
  }

  async delete(id: string): Promise<void> {
    await apiSymfony.delete<void>(API_URLS.DELETE.PAIS(id));
  }
}
