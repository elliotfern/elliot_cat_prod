import { getPageType } from '../../utils/urlPath';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { taulaLlistatContactes } from './taulaLlistatContactes';

const url = window.location.href;
const pageType = getPageType(url);

export function contactes() {
  if ([pageType[1]].includes('agenda-contactes')) {
    taulaLlistatContactes();
  }
}
