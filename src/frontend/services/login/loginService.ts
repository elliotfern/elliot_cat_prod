import type { ApiResponse } from '../../Infrastructure/Api/Client/types';

export type UserRole = 'admin' | 'user';

export interface LoginUser {
  id: string;
  email: string;
  full_name: string;
  role: UserRole;
}

export interface LoginData {
  authenticated: boolean;
  user: LoginUser;
}

export type LoginApiResponse = ApiResponse<LoginData>;

export async function authenticateUser(email: string, password: string): Promise<LoginApiResponse> {
  const response = await fetch('/api/usuaris/post/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    credentials: 'include',
    body: JSON.stringify({ email, password }),
  });

  const payload: unknown = await response.json();

  if (typeof payload !== 'object' || payload === null || !('success' in payload) || typeof payload.success !== 'boolean' || !('message' in payload) || typeof payload.message !== 'string' || !('errors' in payload) || !Array.isArray(payload.errors) || !payload.errors.every((error: unknown): error is string => typeof error === 'string') || !('meta' in payload) || typeof payload.meta !== 'object' || payload.meta === null || !('data' in payload) || typeof payload.data !== 'object' || payload.data === null) {
    throw new Error('Resposta invàlida del servidor');
  }

  const data = payload.data;

  if (!('authenticated' in data) || typeof data.authenticated !== 'boolean' || !('user' in data) || typeof data.user !== 'object' || data.user === null || !('id' in data.user) || typeof data.user.id !== 'string' || !('email' in data.user) || typeof data.user.email !== 'string' || !('full_name' in data.user) || typeof data.user.full_name !== 'string' || !('role' in data.user) || (data.user.role !== 'admin' && data.user.role !== 'user')) {
    throw new Error('Dades d’autenticació invàlides');
  }

  const result: LoginApiResponse = {
    success: payload.success,
    message: payload.message,
    errors: payload.errors,
    meta: payload.meta as Record<string, unknown>,
    data: {
      authenticated: data.authenticated,
      user: {
        id: data.user.id,
        email: data.user.email,
        full_name: data.user.full_name,
        role: data.user.role,
      },
    },
  };

  if (!response.ok || !result.success) {
    const details = result.errors.length > 0 ? `: ${result.errors.join(', ')}` : '';

    throw new Error(`${result.message || "Error d'autenticació"}${details}`);
  }

  return result;
}
