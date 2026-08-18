// franceMusique.ts
// Configuración específica de France Musique para el reproductor genérico.

import { API_BASE } from '../../utils/urls';
import { initRadioPlayer, ProgramaInfo } from './radioPlayer';

interface FranceMusiqueResponse {
  success: boolean;
  message: string;
  errors: unknown[];
  meta: unknown[];

  data: {
    type: string;
    result: string;
  };
}

function parseFranceMusiquePrograma(raw: FranceMusiqueResponse): ProgramaInfo {
  if (!raw.success || !raw.data?.result) {
    throw new Error('La API de France Musique no ha devuelto datos válidos');
  }

  // La propiedad "result" contiene un JSON serializado.
  const data = JSON.parse(raw.data.result) as unknown[];

  /*
   * Estructura específica de France Musique:
   *
   * data[2]  -> startTime
   * data[3]  -> endTime
   * data[23] -> firstLine (nombre del programa)
   * data[25] -> secondLine (título/descripción)
   */

  const startTime = data[2];
  const endTime = data[3];
  const titulo = data[23];
  const descripcion = data[25];

  if (typeof startTime !== 'number' || typeof endTime !== 'number') {
    throw new Error('El horario de France Musique no es válido');
  }

  return {
    titulo: typeof titulo === 'string' ? titulo.trim() : 'France Musique',

    descripcion: typeof descripcion === 'string' ? descripcion.trim() : '',

    inicio: new Date(startTime * 1000),
    fin: new Date(endTime * 1000),
  };
}

export function initFranceMusiquePlayer(): void {
  initRadioPlayer({
    streamUrl: 'https://stream.radiofrance.fr/francemusique/francemusique_hifi.m3u8',

    useHls: true,

    programaApiUrl: `${API_BASE}/radio/get/france-musique`,

    parsePrograma: parseFranceMusiquePrograma,

    logoUrl: 'https://media.elliot.cat/img/web-icones/francemusique.png',

    logoAlt: 'France Musique',
  });
}
