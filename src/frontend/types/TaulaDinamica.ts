export type RenderResult = string | HTMLElement;

export type TaulaDinamica<T extends object> = {
  header: string;
  field: keyof T;
  render?: (value: unknown, row: T) => RenderResult;
};

export type RenderTableOptions<T extends object> = {
  url: string;
  columns: Array<TaulaDinamica<T>>;
  containerId: string;
  rowsPerPage?: number;
  filterKeys?: Array<keyof T>;
  filterByField?: string;

  // ✅ NOU (opt-in): permet split d'un string en múltiples valors per al filtre
  filterSplitBy?: Partial<Record<keyof T, string | RegExp>>;
  filterSplitTrim?: boolean; // default true
  renderHeader?: (raw: unknown) => string;
  dataKey?: string;
};
