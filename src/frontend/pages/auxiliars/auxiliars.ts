import { getPageType } from '../../utils/urlPath';
import { formCiutat } from './formCiutat';
import { formGrupPersones } from './formGrupPersones';
import { formPais } from './formPais';
import { taulaLlistatCiutats } from './taulaLlistatCiutats';
import { taulaLlistatImatges } from './taulaLlistatImatges';
import { taulaLlistatPaisos } from './taulaLlistatPaisos';

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
  } else if ([pageType[2]].includes('llistat-paisos')) {
    taulaLlistatPaisos();
  } else if ([pageType[2]].includes('nou-pais')) {
    formPais(false);
  } else if ([pageType[2]].includes('modifica-pais')) {
    formPais(true, id);
  } else if ([pageType[2]].includes('nou-grup')) {
    formGrupPersones(false);
  }
}
