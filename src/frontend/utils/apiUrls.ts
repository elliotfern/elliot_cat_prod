import { ApiUrls } from '../types/ApiUrl';
import { API_BASE } from './urls';

// Mapa de Endpoints
export const ENDPOINTS = {
  PERFIL_CV_ID: 'perfilCV',
  PERFIL_CV: 'perfilCV',
  PERFIL_CV_I18N_ID: 'perfilCVi18n',
  PERFIL_CV_I18N: 'perfilCVi18n',
  LINK_CV_ID: 'linkCV',
  LINK_CV: 'linkCV',
  LINKS_CV: 'linksCV',
  HABILITAT_ID: 'habilitatId',
  HABILITAT: 'habilitat',
  HABILITATS: 'habilitats',
  EXPERIENCIA_ID: 'experienciaId',
  EXPERIENCIA: 'experiencia',
  EXPERIENCIES: 'experiencies',
} as const;

// Mapa de recursos disponibles
const RESOURCES = {
  CURRICULUM: 'curriculum',
  AUXILIARS: 'auxiliars',
} as const;

const TIPUS = {
  GET: 'get',
  POST: 'post',
  PUT: 'put',
  DELETE: 'delete',
} as const;

export const API_URLS: ApiUrls = {
  GET: {
    PERFIL_CV_ID: (id: number) => `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.GET}/${ENDPOINTS.PERFIL_CV_ID}?id=${encodeURIComponent(id)}`,
    PERFIL_CV_I18N_ID: (perfilId: number, locale: number) => `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.GET}/${ENDPOINTS.PERFIL_CV_I18N_ID}?perfil_id=${encodeURIComponent(perfilId)}&locale=${encodeURIComponent(locale)}`,
    LINK_CV_ID: (id: number) => `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.GET}/${ENDPOINTS.LINK_CV_ID}?id=${encodeURIComponent(id)}`,
    LINKS_CV: `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.GET}/${ENDPOINTS.LINKS_CV}`,
    HABILITAT_ID: (id: number) => `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.GET}/${ENDPOINTS.HABILITAT_ID}?id=${encodeURIComponent(id)}`,
    HABILITATS: `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.GET}/${ENDPOINTS.HABILITATS}`,
    EXPERIENCIA_ID: (id: number) => `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.GET}/${ENDPOINTS.EXPERIENCIA_ID}?id=${encodeURIComponent(id)}`,
  },

  POST: {
    PERFIL_CV: `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.POST}/${ENDPOINTS.PERFIL_CV}`,
    PERFIL_CV_I18N: `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.POST}/${ENDPOINTS.PERFIL_CV_I18N}`,
    LINK_CV: `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.POST}/${ENDPOINTS.LINK_CV}`,
    HABILITAT: `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.POST}/${ENDPOINTS.HABILITAT}`,
    EXPERIENCIA: `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.POST}/${ENDPOINTS.EXPERIENCIA}`,
  },

  PUT: {
    PERFIL_CV: `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.PUT}/${ENDPOINTS.PERFIL_CV}`,
    PERFIL_CV_I18N: `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.PUT}/${ENDPOINTS.PERFIL_CV_I18N}`,
    LINK_CV: `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.PUT}/${ENDPOINTS.LINK_CV}`,
    HABILITAT: `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.PUT}/${ENDPOINTS.HABILITAT}`,
    EXPERIENCIA: `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.PUT}/${ENDPOINTS.EXPERIENCIA}`,
  },

  DELETE: {},
};
