import { API_BASE, API_WEB } from '../../../utils/urls';
import { ApiError, ApiResponse } from './types';
import { buildQueryParams } from './utils';

type QueryParams = Record<string, unknown>;

function createApi(baseUrl: string) {
  return {
    get: async <T>(endpoint: string, queryParams?: QueryParams): Promise<T> => request<T>(baseUrl, endpoint, { method: 'GET' }, queryParams),

    getResponse: async <T>(endpoint: string, queryParams?: QueryParams): Promise<ApiResponse<T>> => requestResponse<T>(baseUrl, endpoint, { method: 'GET' }, queryParams),

    post: async <T>(endpoint: string, body?: unknown, queryParams?: QueryParams): Promise<T> => request<T>(baseUrl, endpoint, { method: 'POST', body: JSON.stringify(body) }, queryParams),

    put: async <T>(endpoint: string, body?: unknown, queryParams?: QueryParams): Promise<T> => request<T>(baseUrl, endpoint, { method: 'PUT', body: JSON.stringify(body) }, queryParams),

    patch: async <T>(endpoint: string, body?: unknown, queryParams?: QueryParams): Promise<T> => request<T>(baseUrl, endpoint, { method: 'PATCH', body: JSON.stringify(body) }, queryParams),

    delete: async <T>(endpoint: string, queryParams?: QueryParams): Promise<T> => request<T>(baseUrl, endpoint, { method: 'DELETE' }, queryParams),
  };
}

export const api = createApi(API_BASE);

export const apiSymfony = createApi(API_WEB);

async function request<T>(baseUrl: string, endpoint: string, options: RequestInit = {}, queryParams?: QueryParams): Promise<T> {
  const response = await requestResponse<T>(baseUrl, endpoint, options, queryParams);

  return response.data;
}

async function requestResponse<T>(baseUrl: string, endpoint: string, options: RequestInit = {}, queryParams?: QueryParams): Promise<ApiResponse<T>> {
  const query = buildQueryParams(queryParams);

  const url = `${baseUrl.replace(/\/+$/, '')}/${endpoint.replace(/^\/+/, '')}${query}`;

  const headers = new Headers({
    'Content-Type': 'application/json',
  });

  new Headers(options.headers).forEach((value, key) => {
    headers.set(key, value);
  });

  let response: Response;

  try {
    response = await fetch(url, {
      ...options,
      headers,
      credentials: 'include',
    });
  } catch {
    throw new ApiError('Error de connexió amb el servidor');
  }

  let json: ApiResponse<T>;

  try {
    json = (await response.json()) as ApiResponse<T>;
  } catch {
    throw new ApiError('Resposta JSON invàlida');
  }

  if (!response.ok || !json.success) {
    throw new ApiError(json.message || 'Error API', json.errors || [], response.status, json);
  }

  return json;
}
