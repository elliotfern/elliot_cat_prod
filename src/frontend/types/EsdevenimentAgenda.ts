type TipusEsdeveniment = 'reunio' | 'visita_medica' | 'videotrucada' | 'altre';
type EstatEsdeveniment = 'pendent' | 'confirmat' | 'cancel·lat';

export interface EsdevenimentAgenda {
  status: string;
  message: string;

  id: number;

  titol: string;
  descripcio: string | null;
  tipus: TipusEsdeveniment;

  lloc: string | null;

  data_inici: string; // backend format
  data_fi: string; // backend format

  tot_el_dia: number; // 0/1
  estat: EstatEsdeveniment;

  creat_el?: string;
  actualitzat_el?: string;
  usuari_id?: number;
}
