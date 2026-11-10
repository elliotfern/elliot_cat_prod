export interface CarpetaCorreu {
  nom: string;
  ruta: string;
  ruta_pare: string | null;
  nom_visual: string;
  tipus: 'inbox' | 'archive' | 'junk' | 'trash' | 'sent' | 'drafts' | 'other';
  total_missatges: number;
  missatges_no_llegits: number;
}

export interface RespostaCarpetes {
  carpetes: CarpetaCorreu[];
}

export interface ContacteCorreu {
  nom: string;
  email: string;
}

export interface MissatgeLlista {
  id: number;
  uid_validity: number;
  carpeta: string;
  remitent: ContacteCorreu;
  destinataris: ContacteCorreu[];
  assumpte: string;
  data: string;
  llegit: boolean;
  destacat: boolean;
  te_adjunts: boolean;
}

export interface MissatgeDetall {
  id: {
    uid: number;
    uid_validity: number;
  };
  carpeta: string;
  remitent: ContacteCorreu;
  destinataris: ContacteCorreu[];
  copia: ContacteCorreu[];
  copia_oculta: ContacteCorreu[];
  assumpte: string;
  contingut_text: string | null;
  contingut_html: string | null;
  data: string;
  llegit: boolean;
  destacat: boolean;
  te_adjunts: boolean;
  message_id: string | null;
  in_reply_to: string | null;
  referencies: string[];
}

export interface MissatgePerEnviar {
  destinataris: ContacteCorreu[];
  copia: ContacteCorreu[];
  copia_oculta: ContacteCorreu[];
  assumpte: string;
  contingut_text: string | null;
  contingut_html: string | null;

  // Metadatos de conversación
  in_reply_to?: string | null;
  referencies?: string[];

  // Contenido original citado o reenviado
  contingut_citat_text?: string | null;
  contingut_citat_html?: string | null;
}

export interface Contacte {
  id: string;
  tipus_persona: string;
  nom: string | null;
  cognoms: string | null;
  empresa: string | null;
  email: string | null;
}

export interface IdentificadorMissatgeCorreu {
  uid: number;
  uid_validity: number;
}

export interface Esborrany {
  id: IdentificadorMissatgeCorreu;
  destinataris: ContacteCorreu[];
  copia: ContacteCorreu[];
  copia_oculta: ContacteCorreu[];
  assumpte: string;
  contingut_text: string | null;
  contingut_html: string | null;
}

export interface EsborranyPerGuardar {
  id?: IdentificadorMissatgeCorreu;
  destinataris: ContacteCorreu[];
  copia: ContacteCorreu[];
  copia_oculta: ContacteCorreu[];
  assumpte: string;
  contingut_text: string | null;
  contingut_html: string | null;
}

export interface RespostaLlistarEsborranys {
  esborranys: Esborrany[];
}

export interface RespostaGuardarEsborrany {
  id: IdentificadorMissatgeCorreu;
}
