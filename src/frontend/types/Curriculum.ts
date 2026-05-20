export interface PerfilCV {
  [key: string]: unknown;
  id: number;
  estat: number;
  perfil_id: number;
  locale: number;
  img_perfil: string;
  localitzacio_ciutat: string;
  email: string;
  nom_complet: string;
  tel: string | null;
  web: string | null;
  ciutat_ca: string | null; // ci.ciutat
  nameImg: string | null; // i.nameImg
  disponibilitat: number | null;
  visibilitat: Vis;
  created_at: string; // ISO
  updated_at: string; // ISO
  adreca: string;
  pais_ca: string;
}

export type Vis = 0 | 1 | boolean;

export interface PerfilCVI18n {
  id: number;
  perfil_id: number;
  locale: number; // 1=ca, 3=es, 2=en, 4=it
  titular: string;
  sumari: string;
}

export interface EducacioCv {
  [key: string]: unknown;
  id: number;
  espai_cat: string;
  municipi: number;
  comarca: number;
  provincia: number;
  comunitat: number;
  estat: number;
  experiencia_id: number;
  institucio_localitzacio: number;
  logo_id: number;
  institucio: string;
  institucio_url?: string | null;
  data_inici?: string | null;
  data_fi?: string | null;
  posicio: number;
  visible: number | boolean;
  created_at: string;
  updated_at: string;

  // extra de la API
  nameImg?: string | null;
  ciutat?: string | null;
  pais_ca?: string | null;
  i18n: EducacioCvI18n[];
}

export interface EducacioCvI18n {
  [key: string]: unknown;
  status: string;
  message: string;
  id: number;
  experiencia_id: number;
  educacio_id: number;
  locale: number;
}

export interface ExperienciaCv {
  [key: string]: unknown;
  id: number;
  logo_empresa: number;
  empresa_localitzacio: number;
  empresa: string;
  empresa_url?: string | null;
  data_inici: string;
  data_fi?: string | null;
  is_current: number | boolean;
  posicio: number;
  visible: number | boolean;
  created_at: string;
  updated_at: string;
  idi18n: number;

  nameImg?: string | null;
  ciutat?: string | null;
  pais_ca?: string | null;

  i18n: ExperienciaCvI18n[];
}

export interface ExperienciaCvI18n {
  [key: string]: unknown;
  experiencia_id: number;
  locale: number;
  fites: string;
  rol_titol: string;
  sumari?: string | null;
  idi18n: number;
}

export interface HabilitatCv {
  [key: string]: unknown;
  estat: number;
  imatge_id: number;
  locale: number;
}

export interface LinkCv {
  [key: string]: unknown;
  estat: number;
  perfil_id: number;
  img_perfil: number;
}

export interface LinkItem {
  id: number;
  perfil_id: number;
  label: string | null;
  url: string;
  posicio: number;
  visible: 0 | 1 | boolean;
  nameImg: string;
}

export interface HabilitatItem {
  id: number;
  nom: string;
  imatge_id?: number | null;
  nameImg?: string | null; // campo devuelto por la API con el nombre del archivo del icono
  posicio: number;
}
