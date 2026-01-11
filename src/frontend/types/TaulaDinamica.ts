export type TaulaDinamica<T extends object> = {
  header: string;
  field: keyof T;
  render?: (value: unknown, row: T) => string;
};

export type RenderTableOptions<T extends object> = {
  url: string;
  columns: Array<TaulaDinamica<T>>;
  containerId: string;
  rowsPerPage?: number;
  filterKeys?: Array<keyof T>;
  filterByField?: keyof T;
};
