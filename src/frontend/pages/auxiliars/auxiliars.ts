import { getPageType } from '../../utils/urlPath';
import { formSubTema } from '../adreces/formSubTema';
import { formTema } from '../adreces/formTema';
import { taulaLlistatSubTemes } from '../adreces/taulaLlistatSubTemes';
import { taulaLlistatTemes } from '../adreces/taulaLlistatTemes';
import { formCiutat } from './formCiutat';
import { formGrupPersones } from './formGrupPersones';
import { formPais } from './formPais';
import { taulaLlistatGrupsPersones } from './llistatGrup';
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
  } else if (pageType[2] === 'nou-grup') {
    formGrupPersones(false);
  } else if (pageType[2] === 'modifica-grup') {
    formGrupPersones(true, id);
  } else if (pageType[2] === 'llistat-grups') {
    taulaLlistatGrupsPersones();
  } else if (pageType[2] === 'llistat-temes') {
    console.log('hola');
    taulaLlistatTemes();
  } else if (pageType[2] === 'llistat-subtemes') {
    taulaLlistatSubTemes();
  } else if (pageType[2] === 'modifica-tema') {
    formTema(true, id);
  } else if (pageType[2] === 'nou-tema') {
    formTema(false);
  } else if (pageType[2] === 'modifica-subtema') {
    formSubTema(true, id);
  } else if (pageType[2] === 'nou-subtema') {
    formSubTema(false);
  }
}
