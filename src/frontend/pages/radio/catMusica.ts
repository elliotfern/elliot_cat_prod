import { API_BASE } from '../../utils/urls';
import { initRadioPlayer, ProgramaInfo } from './radioPlayer';

interface CatMusicaAraFem {
  titol_programa?: string;
  sinopsi?: string;
  start_time: string; // ISO 8601
  end_time: string; // ISO 8601
}

interface CatMusicaCanal {
  ara_fem: CatMusicaAraFem;
}

interface CatMusicaData {
  canal: CatMusicaCanal[];
}

// La API envuelve el resultado real en success/message/errors/meta/data, y
// "data" llega como STRING JSON (hay que hacer JSON.parse), no como objeto.
interface CatMusicaApiResponse {
  success: boolean;
  message: string;
  data: string;
}

function parseCatMusicaPrograma(raw: CatMusicaApiResponse): ProgramaInfo {
  if (!raw.success) {
    throw new Error(raw.message || 'La API ha devuelto success=false');
  }

  const data: CatMusicaData = JSON.parse(raw.data);
  const item = data.canal[0].ara_fem;

  return {
    titulo: item.titol_programa || 'Programa desconocido',
    descripcion: item.sinopsi || '',
    inicio: new Date(item.start_time),
    fin: new Date(item.end_time),
  };
}

export function initCatalunyaMusicaPlayer(): void {
  initRadioPlayer({
    streamUrl: 'https://directes-radio-int.3catdirectes.cat/live-content/catalunya-musica-hls/master.m3u8',
    useHls: true, // requiere hls.js: el HTML no trae el stream en <source>
    programaApiUrl: `${API_BASE}/radio/get/catmusica`,
    parsePrograma: parseCatMusicaPrograma,
    logoUrl: 'https://media.elliot.cat/img/web-icones/catmusica.webp',
    logoAlt: 'Catalunya Música',
  });
}
