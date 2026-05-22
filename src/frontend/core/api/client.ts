import { API_BASE } from '../../utils/urls';
import { ApiError, ApiResponse } from './types';
import { buildQueryParams } from './utils';

export const api = {
  get: async <T>(endpoint: string, queryParams?: Record<string, unknown>): Promise<T> =>
    request<T>(
      endpoint,
      {
        method: 'GET',
      },
      queryParams
    ),

  post: async <T>(endpoint: string, body?: unknown, queryParams?: Record<string, unknown>): Promise<T> =>
    request<T>(
      endpoint,
      {
        method: 'POST',
        body: JSON.stringify(body),
      },
      queryParams
    ),

  put: async <T>(endpoint: string, body?: unknown, queryParams?: Record<string, unknown>): Promise<T> =>
    request<T>(
      endpoint,
      {
        method: 'PUT',
        body: JSON.stringify(body),
      },
      queryParams
    ),

  delete: async <T>(endpoint: string, queryParams?: Record<string, unknown>): Promise<T> =>
    request<T>(
      endpoint,
      {
        method: 'DELETE',
      },
      queryParams
    ),
};

async function request<T>(endpoint: string, options: RequestInit = {}, queryParams?: Record<string, unknown>): Promise<T> {
  const query = buildQueryParams(queryParams);

  let response: Response;

  try {
    response = await fetch(`${API_BASE}${endpoint}${query}`, {
      headers: {
        'Content-Type': 'application/json',
        ...(options.headers || {}),
      },
      credentials: 'include',
      ...options,
    });
  } catch {
    throw new ApiError('Error de connexió amb el servidor');
  }

  let json: ApiResponse<T>;

  try {
    json = await response.json();
  } catch {
    throw new ApiError('Resposta JSON invàlida');
  }

  if (!response.ok || !json.success) {
    throw new ApiError(json.message || 'Error API', json.errors || [], response.status, json);
  }

  return json.data;
}
