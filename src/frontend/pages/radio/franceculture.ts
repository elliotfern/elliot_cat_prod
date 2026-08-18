// franceCulture.ts
// Configuración específica de France Culture para el reproductor genérico.

import { API_BASE } from '../../utils/urls';
import { initRadioPlayer, ProgramaInfo } from './radioPlayer';

interface FranceCultureResponse {
  success: boolean;
  message: string;
  errors: unknown[];
  meta: unknown[];

  data: {
    type: string;
    result: string;
  };
}

function parseFranceCulturePrograma(raw: FranceCultureResponse): ProgramaInfo {
  if (!raw.success || !raw.data?.result) {
    throw new Error('La API de France Culture no ha devuelto datos válidos');
  }

  // La propiedad "result" contiene un JSON serializado.
  const data = JSON.parse(raw.data.result) as unknown[];

  /*
   * Estructura de la respuesta de Radio France:
   *
   * data[0] -> información general
   * data[1] -> definición del programa actual
   * data[2] -> startTime
   * data[3] -> endTime
   * data[19] -> nombre del programa
   * data[21] -> título/descripción del episodio
   */

  const startTime = data[2];
  const endTime = data[3];
  const titulo = data[19];
  const descripcion = data[21];

  if (typeof startTime !== 'number' || typeof endTime !== 'number') {
    throw new Error('El horario de France Culture no es válido');
  }

  return {
    titulo: typeof titulo === 'string' ? titulo.trim() : 'France Culture',

    descripcion: typeof descripcion === 'string' ? descripcion.trim() : '',

    inicio: new Date(startTime * 1000),
    fin: new Date(endTime * 1000),
  };
}

export function initFranceCulturePlayer(): void {
  initRadioPlayer({
    streamUrl: 'https://stream.radiofrance.fr/franceculture/franceculture_hifi.m3u8',
    useHls: true,
    programaApiUrl: `${API_BASE}/radio/get/france-culture`,
    parsePrograma: parseFranceCulturePrograma,
    logoUrl: 'https://media.elliot.cat/img/web-icones/franceculture.webp',
    logoAlt: 'France Culture',
  });
}
