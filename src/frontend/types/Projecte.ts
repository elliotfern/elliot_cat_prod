export type ProjecteDetalls = {
  id: number;
  name: string;
  description: string | null;
  status: number;
  category_id: number | null;
  category_name?: string | null;
  start_date: string;
  end_date: string;
  priority: number;
  client_id: number | null;
  client_name?: string | null;
  budget_id: number | null;
  invoice_id: number | null;
  created_at?: string;
  updated_at?: string;
};

export type TascaItem = {
  id: number;
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
};

export type TasquesResponse = {
  project: { id: number };
  kpis: {
    total: number;
    done: number;
    blocked: number;
    in_progress: number;
    backlog: number;
    next: number;
  };
  page: number;
  limit: number;
  items: TascaItem[];
};
