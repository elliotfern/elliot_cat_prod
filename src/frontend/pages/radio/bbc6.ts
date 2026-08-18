// bbc6.ts
// Configuración específica de BBC Radio 6 Music para el reproductor genérico.

import { API_BASE } from '../../utils/urls';
import { initRadioPlayer, ProgramaInfo } from './radioPlayer';

interface BbcSegment {
  type: string;
  id: string;
  urn: string;
  segment_type: string;

  titles: {
    primary?: string;
    secondary?: string;
    tertiary?: string | null;
    entity_title?: string;
  };

  synopses: unknown;

  image_url?: string;

  offset: {
    start: number;
    end: number;
    label: string;
    now_playing: boolean;
  };

  uris: unknown[];
}

interface BbcApiResponse {
  success: boolean;
  message: string;
  errors: unknown[];
  meta: unknown[];

  data: {
    $schema: string;
    total: number;
    limit: number;
    offset: number;
    data: BbcSegment[];
  };
}

function parseBbc6Programa(raw: BbcApiResponse): ProgramaInfo {
  if (!raw.success || !raw.data?.data?.length) {
    throw new Error('La API de BBC no ha devuelto segmentos');
  }

  const itemActual = raw.data.data.find((item) => item.offset.now_playing === true);

  if (!itemActual) {
    throw new Error('No se ha encontrado ningún elemento en emisión');
  }

  return {
    titulo: itemActual.titles.primary || 'BBC Radio 6 Music',
    descripcion: itemActual.titles.secondary || '',
  };
}

export function initBbc6Player(): void {
  initRadioPlayer({
    streamUrl: 'https://as-hls-ww-live.akamaized.net/pool_81827798/live/ww/bbc_6music/bbc_6music.isml/bbc_6music-audio=96000.norewind.m3u8',

    useHls: true,

    programaApiUrl: `${API_BASE}/radio/get/bbc6`,

    parsePrograma: parseBbc6Programa,

    logoUrl: 'https://media.elliot.cat/img/web-icones/bbc6.jpg',
    logoAlt: 'BBC Radio 6 Music',
  });
}
