import { ApiUrls } from '../types/ApiUrl';
import { API_BASE } from './urls';

// Mapa de Endpoints
export const ENDPOINTS = {
  PERFIL_CV_ID: 'perfilCV',
  PERFIL_CV: 'perfilCV',
  PERFIL_CV_I18N_ID: 'perfilCVi18n',
  PERFIL_CV_I18N: 'perfilCVi18n',
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
    PERFIL_CV_I18N_ID: (id: number) => `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.GET}/${ENDPOINTS.PERFIL_CV_I18N_ID}?id=${encodeURIComponent(id)}`,
  },

  POST: {
    PERFIL_CV: `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.POST}/${ENDPOINTS.PERFIL_CV}`,
    PERFIL_CV_I18N: `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.POST}/${ENDPOINTS.PERFIL_CV_I18N}`,
  },

  PUT: {
    PERFIL_CV: `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.PUT}/${ENDPOINTS.PERFIL_CV}`,
    PERFIL_CV_I18N: `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.PUT}/${ENDPOINTS.PERFIL_CV_I18N}`,
  },

  DELETE: {},
};
