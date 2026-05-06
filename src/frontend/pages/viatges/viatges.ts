import { getPageType } from '../../utils/urlPath';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { taulaLlistatViatges } from './taulaLlistatViatges';
import { taulaLlistatEspaisViatges } from './taulaLlistatEspaisViatge';
import { fitxaEspai } from './fitxaEspai';
import { taulaLlistatVisitesEspais } from './taulaLlistatVisitesEspais';
import { fitxaViatge } from './fitxaViatge';
import { taulaLlistatEspais } from './taulaLlistatEspais';
import { taulaLlistatEspaisVisitats } from './taulaLlistatEspaisVisitats';

const url = window.location.href;
const pageType = getPageType(url);

export function viatges() {
  if (pageType[2] === 'modifica-espai') {
    const autor = document.getElementById('formEspai');
    if (autor) {
      // Lanzar actualizador de datos
      autor.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'PUT', 'formEspai', '/api/viatges/put/?espai');
      });
    }
  } else if (pageType[2] === 'nou-espai') {
    const autor = document.getElementById('formEspai');
    if (autor) {
      // Lanzar actualizador de datos
      autor.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'POST', 'formEspai', '/api/viatges/post/?espai');
      });
    }
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
