import { getPageType } from '../../utils/urlPath';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { taulaLlistatContactes } from './taulaLlistatContactes';
import { formContacte } from './formContacte';

const url = window.location.href;
const pageType = getPageType(url);

export function contactes() {
  if (pageType[2] === 'modifica-contacte') {
    const uuid = pageType[3];
    formContacte(true, String(uuid));
  } else if (pageType[2] === 'nou-contacte') {
    formContacte(false);
  } else if (pageType[1] === 'agenda-contactes') {
    taulaLlistatContactes();
  }
}
