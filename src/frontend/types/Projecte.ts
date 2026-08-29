export type ProjecteEstat = 'pendent' | 'en_curs' | 'finalitzat' | 'arxivat';

export type ProjecteCategoria = 'professional' | 'personal';

export type ProjectePrioritat = 'baixa' | 'normal' | 'alta' | 'urgent';

export type ProjecteDetalls = {
  id: string;
  projecte: string;
  descripcio: string | null;
  data_inici: string;
  data_fi: string;
  pressupost_id: number | null;
  factura_id: number | null;
  created_at?: string;
  updated_at?: string;

  estat: ProjecteEstat;
  categoria: ProjecteCategoria;
  prioritat: ProjectePrioritat;

  client_id: number | null;
  nom?: string | null;
  cognoms?: string | null;
  empresa?: string | null;
};

export type TascaItem = {
  id: string;
  project_id: number | null;
  title: string;
  subject: string | null;
  notes: string | null;
  status: number;
  priority: number;
  planned_date: string;
  is_next: number;
  blocked_reason: string | null;
  estimated_hours: string | number | null;
  created_at?: string;
  updated_at?: string;
  done_at?: string | null;
  estat: string;
  prioritat: string;
};

export type TasquesResponse = {
  project: { id: number };
  kpis: {
    done_at: number;
    blocked: number;
    in_progress: number;
    backlog: number;
    next: number;
  };
  page: number;
  limit: number;
  items: TascaItem[];
};
