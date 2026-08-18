// bbc4.ts
// Configuración específica de BBC Radio 4 para el reproductor genérico.
//
// Sin información de "programa en emisión": la API oficial (RMS) requiere
// autenticación privada que no tenemos, y el scraping del reproductor web
// es frágil (HTML sujeto a cambios, posible renderizado por JS, geobloqueo
// entre bbc.co.uk/sounds y bbc.com/audio). Por eso no se pasan programaApiUrl
// ni parsePrograma: radioPlayer.ts, al no recibirlos, muestra solo un texto
// fijo ("BBC Radio 4 · En directo") sin hacer ningún fetch.

import { initRadioPlayer } from './radioPlayer';

export function initBbc4Player(): void {
  initRadioPlayer({
    streamUrl: 'http://as-hls-ww-live.akamaized.net/pool_55057080/live/ww/bbc_radio_fourfm/bbc_radio_fourfm.isml/bbc_radio_fourfm-audio%3d96000.norewind.m3u8',
    useHls: true, // es un .m3u8, necesita hls.js
    logoUrl: 'https://media.elliot.cat/img/web-icones/bbc4.png',
    logoAlt: 'BBC Radio 4',
    // ids e initControls por defecto: audio="audio", .controls-radio con ▶️⏸️🔇🔊🔉
  });
}
