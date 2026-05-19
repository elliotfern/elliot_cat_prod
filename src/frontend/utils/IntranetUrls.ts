import { IntranetUrls } from '../types/IntranetUrls';
import { INTRANET_WEB } from './urls';

// Mapa de Endpoints
export const ENDPOINTS = {
  EMISSOR_MODIFICA: 'modifica-emissor',
  EMISSOR_FITXA: 'fitxa-emissor',
} as const;

// Mapa de recursos disponibles
const MODUL = {
  CURRICULUM: 'curriculum',
  AUXILIARS: 'auxiliars',
  CIUTATS: 'ciutats',
  COMPTABILITAT: 'comptabilitat',
  ADRECES: 'adreces',
  AGENDA: 'agenda',
  PERSONA: 'persones',
  PROJECTES: 'projectes',
  BLOG: 'blog',
  HISTORIA: 'historia',
} as const;

export const INTRANET_URLS: IntranetUrls = {
  COMPTABILITAT: {
    EMISSOR_FITXA_ID: (id: string) => `${INTRANET_WEB}/${MODUL.CURRICULUM}/${ENDPOINTS.EMISSOR_FITXA}/${encodeURIComponent(id)}`,
    EMISSOR_MODIFICA_ID: (id: string) => `${INTRANET_WEB}/${MODUL.CURRICULUM}/${ENDPOINTS.EMISSOR_MODIFICA}/${encodeURIComponent(id)}`,
  },
};
