export interface Tasca {
  [key: string]: unknown;

  id: string;
  projecte_id: string;
  title: string;
  subject: string | null;
  notes: string | null;
  estat: 'pendent' | 'en_curs' | 'finalitzat' | 'arxivat';
  prioritat: 'baixa' | 'normal' | 'alta' | 'urgent';
  planned_date: string | null;
  is_next: number;
  blocked_reason: string | null;
  estimated_hours: string | null;
  created_at?: string;
  updated_at?: string;
  done_at?: string | null;
  client_id: string;
}
