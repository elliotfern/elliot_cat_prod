export function buildQueryParams(params?: Record<string, unknown>): string {
  if (!params) return '';

  const searchParams = new URLSearchParams();

  Object.entries(params).forEach(([key, value]) => {
    /*
     * Ignorar null/undefined
     */

    if (value === undefined || value === null) {
      return;
    }

    /*
     * Arrays
     */

    if (Array.isArray(value)) {
      value.forEach((item) => {
        searchParams.append(key, String(item));
      });

      return;
    }

    /*
     * Boolean/int/string
     */

    searchParams.append(key, String(value));
  });

  const query = searchParams.toString();

  return query ? `?${query}` : '';
}
