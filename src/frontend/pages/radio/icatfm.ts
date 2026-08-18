// icatfm.ts
// Configuración específica de iCat FM
// para el reproductor genérico.

import { API_BASE } from '../../utils/urls';
import { initRadioPlayer, ProgramaInfo } from './radioPlayer';

interface IcatCanal {
  ara_fem: IcatAraFem;
}

interface IcatData {
  canal: IcatCanal[];
}

interface IcatApiResponse {
  success: boolean;
  message: string;
  data: string;
}

interface IcatAraSona {
  titol?: string;
  artista?: string;
  start_time?: string;
  end_time?: string;
  durada?: string;
}

interface IcatAraFem {
  titol_programa?: string;
  sinopsi?: string;
  start_time: string;
  end_time: string;
  presentador?: string;
  ara_sona?: IcatAraSona;
}

function parseIcatPrograma(raw: IcatApiResponse): ProgramaInfo {
  if (!raw.success) {
    throw new Error(raw.message || 'La API de iCat FM ha devuelto success=false');
  }

  const data: IcatData = JSON.parse(raw.data);

  const item = data.canal[0].ara_fem;

  const programa = item.titol_programa || 'iCat FM';
  const presentador = item.presentador || '';

  const araSona = item.ara_sona;

  let descripcion = '';

  if (presentador) {
    descripcion += `Presentadors: ${presentador}`;
  }

  if (araSona?.titol) {
    if (descripcion) {
      descripcion += '<br>';
    }

    descripcion += `<strong>Ara sona:</strong> ${araSona.titol}`;

    if (araSona.artista) {
      descripcion += ` — ${araSona.artista}`;
    }
  }

  return {
    titulo: programa,
    descripcion,
    inicio: new Date(item.start_time),
    fin: new Date(item.end_time),
  };
}

export function initIcatFmPlayer(): void {
  initRadioPlayer({
    streamUrl: 'https://directes-radio-int.3catdirectes.cat/live-content/icat-hls/master.m3u8',

    useHls: true,

    programaApiUrl: `${API_BASE}/radio/get/icatfm`,

    parsePrograma: parseIcatPrograma,

    logoUrl: 'https://media.elliot.cat/img/web-icones/icatfm.png',

    logoAlt: 'iCat FM',
  });
}
