export interface ExperienciaItem {
  id: number;
  empresa: string;
  empresa_url?: string | null;
  empresa_localitzacio?: number | null;
  data_inici: string;
  data_fi?: string | null;
  is_current: 0 | 1 | boolean;
  logo_empresa?: number | null;
  posicio: number;
  visible: 0 | 1 | boolean;
}
