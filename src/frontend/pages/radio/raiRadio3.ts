// raiRadio3.ts
// Configuración específica de Rai Radio 3 para el reproductor genérico.

import { initRadioPlayer, ProgramaInfo } from './radioPlayer';

interface RaiCurrentItem {
  name?: string;
  episode_title?: string;
  hour: string; // formato "HH:MM"
  duration: string; // formato "HH:MM:SS"
}

interface RaiOnAirEntry {
  currentItem: RaiCurrentItem;
}

interface RaiOnAirResponse {
  on_air: RaiOnAirEntry[];
}

function parseRaiPrograma(raw: RaiOnAirResponse): ProgramaInfo {
  const radio3 = raw.on_air[2];
  const item = radio3.currentItem;

  const ahora = new Date();
  const [h, m] = item.hour.split(':').map(Number);
  const [dh, dm, ds] = item.duration.split(':').map(Number);

  const inicio = new Date(ahora);
  inicio.setHours(h, m, 0, 0);
  if (inicio > ahora) {
    inicio.setDate(inicio.getDate() - 1);
  }

  const fin = new Date(inicio);
  fin.setHours(fin.getHours() + dh);
  fin.setMinutes(fin.getMinutes() + dm);
  fin.setSeconds(fin.getSeconds() + ds);

  return {
    titulo: item.name || 'Programa desconocido',
    descripcion: item.episode_title || '',
    inicio,
    fin,
  };
}

export function initRaiRadio3Player(): void {
  initRadioPlayer({
    streamUrl: 'https://icecdn-19d24861e90342cc8decb03c24c8a419.msvdn.net/icecastRelay/S56630579/yEbkcBtIoSwd/icecast',
    useHls: false, // el <audio><source> del HTML ya apunta a este stream
    programaApiUrl: 'https://www.raiplaysound.it/palinsesto/onAir.json',
    parsePrograma: parseRaiPrograma,
    logoUrl: 'https://media.elliot.cat/img/web-icones/rai_radio_3.svg',
    logoAlt: 'Rai Radio 3',
  });
}
