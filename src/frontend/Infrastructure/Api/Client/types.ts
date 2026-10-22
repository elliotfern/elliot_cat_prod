export interface ApiResponse<T> {
  success: boolean;
  message: string;
  errors: string[];
  meta: Record<string, unknown>;
  data: T;
}

export class ApiError extends Error {
  constructor(
    public message: string,
    public errors: string[] = [],
    public statusCode?: number,
    public payload?: unknown
  ) {
    super(message);

    this.name = 'ApiError';
  }
}
