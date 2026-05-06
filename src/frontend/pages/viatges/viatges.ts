import { getPageType } from '../../utils/urlPath';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { taulaLlistatViatges } from './taulaLlistatViatges';
import { taulaLlistatEspaisViatges } from './taulaLlistatEspaisViatge';
import { fitxaEspai } from './fitxaEspai';
import { taulaLlistatVisitesEspais } from './taulaLlistatVisitesEspais';
import { fitxaViatge } from './fitxaViatge';
import { taulaLlistatEspais } from './taulaLlistatEspais';
import { taulaLlistatEspaisVisitats } from './taulaLlistatEspaisVisitats';
import { formEspai } from './formEspai';

const url = window.location.href;
const pageType = getPageType(url);

export function viatges() {
  if (pageType[2] === 'modifica-espai') {
    const slug = pageType[3];
    formEspai(true, slug);
  } else if (pageType[2] === 'nou-espai') {
    formEspai(false);
  } else if ([pageType[1], pageType[2]].includes('llistat-viatges')) {
    taulaLlistatViatges();
  } else if ([pageType[1], pageType[2]].includes('llistat-espais')) {
    taulaLlistatEspais();
  } else if ([pageType[1], pageType[2]].includes('llistat-espais-visitats')) {
    taulaLlistatEspaisVisitats();
  } else if ([pageType[1], pageType[2]].includes('fitxa-viatge')) {
    fitxaViatge();
    taulaLlistatEspaisViatges();
  } else if ([pageType[1], pageType[2]].includes('fitxa-espai')) {
    fitxaEspai(); // se ejecuta cuando Leaflet está cargado
    taulaLlistatVisitesEspais();
  }
}
