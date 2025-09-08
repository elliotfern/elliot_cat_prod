import { ApiUrls } from '../types/ApiUrl';
import { API_BASE } from './urls';

// Mapa de Endpoints
export const ENDPOINTS = {
  PERFIL_CV_ID: 'perfilCV',
  PERFIL_CV: 'perfilCV',
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
  },

  POST: {
    PERFIL_CV: `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.POST}/${ENDPOINTS.PERFIL_CV}`,
  },

  PUT: {
    PERFIL_CV: `${API_BASE}/${RESOURCES.CURRICULUM}/${TIPUS.PUT}/${ENDPOINTS.PERFIL_CV}`,
  },

  DELETE: {},
};
