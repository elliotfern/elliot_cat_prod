export type RenderResult = string | HTMLElement;

export type TaulaDinamica<T extends object> = {
  header: string;
  field: keyof T;
  render?: (value: unknown, row: T) => RenderResult;
  sortValue?: (row: T) => string | number | null;
};

export type RenderTableOptions<T extends object> = {
  url: string;
  columns: Array<TaulaDinamica<T>>;
  containerId: string;
  rowsPerPage?: number;
  filterKeys?: Array<keyof T>;

  // Filtro simple — se mantiene por compatibilidad
  filterByField?: string;

  // Nuevo: permite varios niveles de filtrado
  filterByFields?: string[];

  // Split de valores para filtros
  filterSplitBy?: Partial<Record<keyof T, string | RegExp>>;
  filterSplitTrim?: boolean;

  renderHeader?: (raw: unknown) => string;
  dataKey?: string;
  filterLabels?: Record<string, string>;
};
