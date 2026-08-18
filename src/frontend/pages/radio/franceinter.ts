// franceInter.ts
// Configuración específica de France Inter para el reproductor genérico.

import { API_BASE } from '../../utils/urls';
import { initRadioPlayer, ProgramaInfo } from './radioPlayer';

interface FranceInterResponse {
  success: boolean;
  message: string;
  errors: unknown[];
  meta: unknown[];

  data: {
    type: string;
    result: string;
  };
}

function parseFranceInterPrograma(raw: FranceInterResponse): ProgramaInfo {
  if (!raw.success || !raw.data?.result) {
    throw new Error('La API de France Inter no ha devuelto datos válidos');
  }

  // La propiedad "result" contiene un JSON serializado.
  const data = JSON.parse(raw.data.result) as unknown[];

  /*
   * Estructura de la respuesta de Radio France:
   *
   * data[2]  -> startTime
   * data[3]  -> endTime
   * data[19] -> nombre del programa
   * data[21] -> título/descripción del episodio
   */

  const startTime = data[2];
  const endTime = data[3];
  const titulo = data[19];
  const descripcion = data[21];

  if (typeof startTime !== 'number' || typeof endTime !== 'number') {
    throw new Error('El horario de France Inter no es válido');
  }

  return {
    titulo: typeof titulo === 'string' ? titulo.trim() : 'France Inter',

    descripcion: typeof descripcion === 'string' ? descripcion.trim() : '',

    inicio: new Date(startTime * 1000),
    fin: new Date(endTime * 1000),
  };
}

export function initFranceInterPlayer(): void {
  initRadioPlayer({
    streamUrl: 'https://stream.radiofrance.fr/franceinter/franceinter_hifi.m3u8',

    useHls: true,

    programaApiUrl: `${API_BASE}/radio/get/france-inter`,

    parsePrograma: parseFranceInterPrograma,

    logoUrl: 'https://media.elliot.cat/img/web-icones/franceinter.png',

    logoAlt: 'France Inter',
  });
}
