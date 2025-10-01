import { getPageType } from '../../utils/urlPath';
import { taulaLlistatLinks } from './taulaLlistatLinks';
import { taulaLlistatSubTemes } from './taulaLlistatSubTemes';
import { taulaLlistatTemes } from './taulaLlistatTemes';
import { formTema } from './formTema';
import { formSubTema } from './formSubTema';
import { formLink } from './formLink';
import { taulaLlistatSubTemaId } from './taulaLlistatSubTemaId';

const url = window.location.href;
const pageType = getPageType(url);

export function adreces() {
  const id = parseInt(pageType[3], 10);
  const uuid = pageType[3];
  if (pageType[2] === 'modifica-link') {
    formLink(true, uuid);
  } else if (pageType[2] === 'nou-link') {
    formLink(false);
  } else if (pageType[2] === 'llistat-temes') {
    taulaLlistatTemes();
  } else if (pageType[2] === 'llistat-links') {
    taulaLlistatLinks();
  } else if (pageType[2] === 'llistat-subtemes') {
    taulaLlistatSubTemes();
  } else if (pageType[2] === 'llistat-subtema') {
    taulaLlistatSubTemaId(uuid);
  } else if (pageType[2] === 'modifica-tema') {
    formTema(true, uuid);
  } else if (pageType[2] === 'nou-tema') {
    formTema(false);
  } else if (pageType[2] === 'modifica-subtema') {
    formSubTema(true, uuid);
  } else if (pageType[2] === 'nou-subtema') {
    formSubTema(false);
  }
}
