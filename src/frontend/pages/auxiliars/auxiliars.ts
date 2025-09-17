import { getPageType } from '../../utils/urlPath';
import { formCiutat } from './formCiutat';
import { taulaLlistatCiutats } from './taulaLlistatCiutats';
import { taulaLlistatImatges } from './taulaLlistatImatges';

const url = window.location.href;
const pageType = getPageType(url);

export function auxiliars() {
  const id = pageType[3];
  if ([pageType[2]].includes('llistat-imatges')) {
    taulaLlistatImatges();
  } else if ([pageType[2]].includes('nova-ciutat')) {
    formCiutat(false);
  } else if ([pageType[2]].includes('modifica-ciutat')) {
    formCiutat(true, id);
  } else if ([pageType[2]].includes('llistat-ciutats')) {
    taulaLlistatCiutats();
  }
}
