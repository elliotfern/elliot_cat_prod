// rneRadio3.ts
// Configuración específica de Radio 3 para el reproductor genérico.

import { API_BASE } from '../../utils/urls';
import { initRadioPlayer, ProgramaInfo } from './radioPlayer';

interface Radio3Programa {
  id: string;
  sgce: string;
  nombre: string;
  descripcion: string;
  txt: string;
  presentador: string;
  director: string;
  equipo: string;
  imagen: string;
  hora: string;
  horaFin: string;
  isNow: boolean;
  dia: string;
  fechaInicio: string;
}

interface Radio3Response {
  success: boolean;
  message: string;
  errors: unknown[];
  meta: unknown[];

  data: {
    emisiones: Array<{
      programas: {
        items: Radio3Programa[];
      };
    }>;
  };
}

function parseRadio3RNEPrograma(raw: Radio3Response): ProgramaInfo {
  if (!raw.success || !raw.data?.emisiones?.length) {
    throw new Error('La API de Radio 3 RNE no ha devuelto datos válidos');
  }

  const programas = raw.data.emisiones[0]?.programas?.items;

  if (!programas || programas.length === 0) {
    throw new Error('No se han encontrado programas de Radio 3 RNE');
  }

  // El item 0 corresponde al programa actualmente en emisión.
  const programa = programas[0];

  return {
    titulo: programa.nombre?.trim() || 'Radio 3 RNE',

    descripcion: programa.descripcion?.trim() || '',

    inicio: convertirHoraADate(programa.hora),
    fin: convertirHoraADate(programa.horaFin),

    presentador: programa.presentador?.trim() || '',
  };
}

function convertirHoraADate(hora: string): Date {
  const [hores, minuts] = hora.split(':').map(Number);

  const data = new Date();

  data.setHours(hores, minuts, 0, 0);

  return data;
}

export function initRadio3RNEPlayer(): void {
  initRadioPlayer({
    streamUrl: 'https://rtvelivestream.rtve.es/rtvesec/rne/rne_r3_main_dvr.m3u8',

    useHls: true,

    programaApiUrl: `${API_BASE}/radio/get/radio3-rne`,

    parsePrograma: parseRadio3RNEPrograma,

    logoUrl: 'https://media.elliot.cat/img/web-icones/radio3rne.png',

    logoAlt: 'Radio 3 RNE',
  });
}
