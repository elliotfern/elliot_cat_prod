export interface BlogArticle {
  [key: string]: unknown;

  id: number;
  post_type: string;
  post_title: string;
  post_content: string;
  post_excerpt?: string | null;
  lang: number; // int(1)
  post_status: string;
  slug: string;
  categoria: string; // binary(16) -> normalment ho representarem com hex/uuid string al frontend
  post_date: string; // datetime
  post_modified: string; // datetime
}
