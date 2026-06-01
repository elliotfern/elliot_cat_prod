export interface Tasca {
  [key: string]: unknown;

  // DB
  id: number;
  project_id: number | null;

  title: string;
  subject: string | null;
  notes: string | null;

  status: number; // tinyint unsigned (default 1)
  priority: number; // tinyint unsigned (default 3)

  planned_date: string | null; // YYYY-MM-DD
  is_next: number; // tinyint(1) default 0

  blocked_reason: string | null;
  estimated_hours: number | null; // decimal(6,2)

  // Meta (puede venir en GET)
  created_at?: string;
  updated_at?: string;
  done_at?: string | null;
}
