import type { ContacteCorreu } from './Correu';

export interface OpcionsRedaccio {
  destinataris?: ContacteCorreu[];
  copia?: ContacteCorreu[];
  assumpte?: string;
  inReplyTo?: string | null;
  referencies?: string[];
  contingutCitatText?: string | null;
  contingutCitatHtml?: string | null;
}
