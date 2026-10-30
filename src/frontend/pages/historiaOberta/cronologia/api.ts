import { api } from '../../../Infrastructure/Api/Client/ApiClient';
import { Esdeveniment } from '../../../types/Esdeveniment';
import { Etapa } from '../../../types/EtapaHistorica';
import { Subetapa } from '../../../types/SubEtapaHistorica';

export function getEventos(etapa?: string, subetapa?: string) {
  const params: Record<string, unknown> = {};

  if (etapa) {
    params.etapa = etapa;
  }

  if (subetapa) {
    params.subetapa = subetapa;
  }

  return api.getResponse<Esdeveniment[]>('historia/get/llistatEsdeveniments', params);
}

export function getSubetapas(etapa: string) {
  return api.getResponse<Subetapa[]>('historia/get/subEtapesEtapa', { id: etapa });
}

export function getEtapas() {
  return api.getResponse<Etapa[]>('historia/get/llistatEtapes');
}
