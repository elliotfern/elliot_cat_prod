export interface Evento {
  slug: string;
  esdeNom: string;
  ciutat: string;
  pais_cat: string;
  etapaNom: string;
  nomSubEtapa: string;
  esdeDataIDia: number;
  esdeDataIMes: number;
  esdeDataIAny: number;
  esdeDataFDia: number;
  esdeDataFMes: number;
  esdeDataFAny: number;
}

export const state = {
  eventos: [] as Evento[],
  paginaActual: 1,
  eventosPorPagina: 15,
  etapa: 1,
  subetapa: null as string | null,
};
