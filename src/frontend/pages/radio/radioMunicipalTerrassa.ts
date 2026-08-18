// radioMunicipalTerrassa.ts
// Configuración específica de Ràdio Municipal de Terrassa.

import { initRadioPlayer } from './radioPlayer';

export function initRadioMunicipalTerrassaPlayer(): void {
  initRadioPlayer({
    streamUrl: 'https://control.streaming-pro.com:8002/stream',

    useHls: false,

    logoUrl: 'https://media.elliot.cat/img/web-icones/radiomunicipalterrassa.png',

    logoAlt: 'Ràdio Municipal de Terrassa',
  });
}
