// catalunyaInformacio.ts
// Configuración específica de Catalunya Informació
// para el reproductor genérico.

import { API_BASE } from '../../utils/urls';
import { initRadioPlayer, ProgramaInfo } from './radioPlayer';

interface CatInfoAraFem {
  titol_programa?: string;
  sinopsi?: string;
  start_time: string;
  end_time: string;
}

interface CatInfoCanal {
  ara_fem: CatInfoAraFem;
}

interface CatInfoData {
  canal: CatInfoCanal[];
}

interface CatInfoApiResponse {
  success: boolean;
  message: string;
  data: string;
}

function parseCatInfoPrograma(raw: CatInfoApiResponse): ProgramaInfo {
  if (!raw.success) {
    throw new Error(raw.message || 'La API de Catalunya Informació ha devuelto success=false');
  }

  const data: CatInfoData = JSON.parse(raw.data);

  const item = data.canal[0].ara_fem;

  return {
    titulo: item.titol_programa || 'Catalunya Informació',
    descripcion: item.sinopsi || '',
    inicio: new Date(item.start_time),
    fin: new Date(item.end_time),
  };
}

export function initCatalunyaInformacioPlayer(): void {
  initRadioPlayer({
    streamUrl: 'https://directes-radio-int.3catdirectes.cat/live-content/catalunya-informacio-hls/master.m3u8',

    useHls: true,

    programaApiUrl: `${API_BASE}/radio/get/catinfo`,

    parsePrograma: parseCatInfoPrograma,

    logoUrl: 'https://media.elliot.cat/img/web-icones/3catinfo.png',

    logoAlt: 'Catalunya Informació',
  });
}
