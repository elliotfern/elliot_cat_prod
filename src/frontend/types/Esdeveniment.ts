export interface Esdeveniment {
  id: string;
  slug: string;
  esdeNom: string;

  ciutat: string | null;
  pais: string | null;
  etapaNom: string | null;
  nomSubEtapa: string | null;

  esdeDataIDia: number;
  esdeDataIMes: number;
  esdeDataIAny: number;
  esdeDataFDia: number;
  esdeDataFMes: number;
  esdeDataFAny: number;

  descripcio: string;

  dateCreated: string;
  dateModified: string;
  nameImg: string | null;
  alt: string | null;
  persones: EsdevenimentPersona[];
  organitzacions: EsdevenimentOrganitzacio[];

  nom: string;
  imatge_id: string | null;

  data_inici_dia: number | null;
  data_inici_mes: number | null;
  data_inici_any: number | null;

  data_final_dia: number | null;
  data_final_mes: number | null;
  data_final_any: number | null;

  sub_etapa_id: string | null;
  ciutat_id: string | null;
}

export interface EsdevenimentPersona {
  id: string;
  nom: string;
  cognoms: string;
  slug: string;
}

export interface EsdevenimentOrganitzacio {
  id: string;
  organitzacio: string;
  sigla: string;
  slug: string;
}

export const state = {
  eventos: [] as Esdeveniment[],
  paginaActual: 1,
  eventosPorPagina: 15,
  etapa: '' as string,
  subetapa: null as string | null,
};
