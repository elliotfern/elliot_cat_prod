export interface Carrec {
  id: string;
  nom: string;
  inici: number | null;
  fi: number | null;
  organitzacio_id: string | null;
  organitzacio: string | null;
  slugOrganitzacio: string;
  nomPersona: string | null;
  cognoms: string | null;
  slugPersona: string;
  persona_id: string;
}
