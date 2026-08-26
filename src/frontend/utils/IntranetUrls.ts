import { IntranetUrls } from '../types/IntranetUrls';
import { INTRANET_WEB } from './urls';

// Mapa de Endpoints
export const ENDPOINTS = {
  EMISSOR_MODIFICA: 'modifica-emissor',
  EMISSOR_FITXA: 'fitxa-emissor',
  CLIENT_MODIFICA: 'modifica-client',
  CLIENT_FITXA: 'fitxa-client',
  PROVEIDOR_MODIFICA: 'modifica-proveidor',
  PROVEIDOR_FITXA: 'fitxa-proveidor',
  PRESSUPOST_MODIFICA: 'modifica-pressupost',
  CONTACTE_MODIFICA: 'modifica-contacte',
  FACTURA_MODIFICA: 'modifica-factura',
  FACTURA_DESPESA_MODIFICA: 'modifica-factura-proveidor',
  PRODUCTE_MODIFICA: 'modifica-producte',
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
  CONTACTES: 'agenda-contactes',
} as const;

export const INTRANET_URLS: IntranetUrls = {
  COMPTABILITAT: {
    EMISSOR_FITXA_ID: (id: string) => `${INTRANET_WEB}/${MODUL.COMPTABILITAT}/${ENDPOINTS.EMISSOR_FITXA}/${encodeURIComponent(id)}`,
    EMISSOR_MODIFICA_ID: (id: string) => `${INTRANET_WEB}/${MODUL.COMPTABILITAT}/${ENDPOINTS.EMISSOR_MODIFICA}/${encodeURIComponent(id)}`,
    CLIENT_FITXA_ID: (id: string) => `${INTRANET_WEB}/${MODUL.COMPTABILITAT}/${ENDPOINTS.CLIENT_FITXA}/${encodeURIComponent(id)}`,
    CLIENT_MODIFICA_ID: (id: string) => `${INTRANET_WEB}/${MODUL.COMPTABILITAT}/${ENDPOINTS.CLIENT_MODIFICA}/${encodeURIComponent(id)}`,
    PROVEIDOR_FITXA_ID: (id: string) => `${INTRANET_WEB}/${MODUL.COMPTABILITAT}/${ENDPOINTS.PROVEIDOR_FITXA}/${encodeURIComponent(id)}`,
    PROVEIDOR_MODIFICA_ID: (id: string) => `${INTRANET_WEB}/${MODUL.COMPTABILITAT}/${ENDPOINTS.PROVEIDOR_MODIFICA}/${encodeURIComponent(id)}`,
    PRESSUPOST_MODIFICA_ID: (id: string) => `${INTRANET_WEB}/${MODUL.COMPTABILITAT}/${ENDPOINTS.PRESSUPOST_MODIFICA}/${encodeURIComponent(id)}`,
    FACTURA_MODIFICA_ID: (id: number) => `${INTRANET_WEB}/${MODUL.COMPTABILITAT}/${ENDPOINTS.FACTURA_MODIFICA}/${encodeURIComponent(id)}`,
    FACTURA_DESPESA_MODIFICA: (id: string) => `${INTRANET_WEB}/${MODUL.COMPTABILITAT}/${ENDPOINTS.FACTURA_DESPESA_MODIFICA}/${encodeURIComponent(id)}`,
    PRODUCTE_MODIFICA: (id: string) => `${INTRANET_WEB}/${MODUL.COMPTABILITAT}/${ENDPOINTS.PRODUCTE_MODIFICA}/${encodeURIComponent(id)}`,
  },

  CONTACTES: {
    CONTACTE_MODIFICA_ID: (id: string) => `${INTRANET_WEB}/${MODUL.CONTACTES}/${ENDPOINTS.CONTACTE_MODIFICA}/${encodeURIComponent(id)}`,
  },
};
