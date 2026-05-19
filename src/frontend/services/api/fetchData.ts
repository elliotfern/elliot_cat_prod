import { API_BASE } from '../../utils/urls';

export async function fetchDataGet<T>(relativeUrl: string, url?: boolean): Promise<T | null> {
  let fullUrl = '';
  if (!url) {
    fullUrl = `${API_BASE}${relativeUrl}`;
  } else {
    fullUrl = `${relativeUrl}`;
  }

  try {
    const response = await fetch(fullUrl, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
    });
    if (!response.ok) {
      console.error('Error en la respuesta HTTP', response.status);
      return null;
    }
    const result = await response.json();
    return result as T;
  } catch (error) {
    console.error('Error en fetchDataGet:', error);
    return null;
  }
}

// apiClient.ts

type ApiResponse<T> = {
  success: boolean;
  message?: string;
  data: T;
  errors?: any[];
  meta?: any;
};

class ApiError extends Error {
  public errors: any[];
  public meta?: any;

  constructor(message: string, errors: any[] = [], meta?: any) {
    super(message);
    this.name = 'ApiError';
    this.errors = errors;
    this.meta = meta;
  }
}

async function request<T>(url: string, options: RequestInit = {}): Promise<T> {
  const res = await fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      ...(options.headers || {}),
    },
    ...options,
  });

  let json: ApiResponse<T>;

  try {
    json = await res.json();
  } catch (e) {
    throw new ApiError('Invalid JSON response');
  }

  // HTTP error (404, 500, etc.)
  if (!res.ok) {
    throw new ApiError(json?.message || 'HTTP Error', json?.errors || [], json?.meta);
  }

  // API error (success: false)
  if (!json.success) {
    throw new ApiError(json.message || 'API Error', json.errors || [], json.meta);
  }

  return json.data;
}

// Helpers por método
export const apiClient = {
  get: <T>(url: string) => request<T>(url),

  post: <T>(url: string, body: any) =>
    request<T>(url, {
      method: 'POST',
      body: JSON.stringify(body),
    }),

  put: <T>(url: string, body: any) =>
    request<T>(url, {
      method: 'PUT',
      body: JSON.stringify(body),
    }),

  delete: <T>(url: string) =>
    request<T>(url, {
      method: 'DELETE',
    }),
};

export { ApiError };
