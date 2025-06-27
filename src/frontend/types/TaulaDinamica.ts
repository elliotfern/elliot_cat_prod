export type TaulaDinamica<T> = {
  header: string;
  field: keyof T;
  render?: (value: T[keyof T], row: T) => string;
};

export type RenderTableOptions<T> = {
  url: string;
  columns: TaulaDinamica<T>[];
  containerId: string;
  rowsPerPage?: number;
  filterKeys?: (keyof T)[];
  filterByField?: keyof T;
};
