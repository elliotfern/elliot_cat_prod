import { getPageType } from '../../utils/urlPath';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { taulaLlistatTemes } from './taulaLlistatCategories';
import { taulaLlistatAdrecesPerTema } from './taulaLlistatAdrecesPerTema';
import { taulaLlistatLinks } from './taulaLlistatLinks';

const url = window.location.href;
const pageType = getPageType(url);

export function adreces() {
  if (pageType[2] === 'modifica-link') {
    const autor = document.getElementById('modificalink');
    if (autor) {
      // Lanzar actualizador de datos
      autor.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'PUT', 'modificalink', '/api/adreces/put/?link');
      });
    }
  } else if (pageType[2] === 'nou-link') {
    const llibre = document.getElementById('modificalink');
    if (llibre) {
      // Lanzar actualizador de datos
      llibre.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'POST', 'modificalink', '/api/adreces/post/?link');
      });
    }
  } else if ([pageType[1], pageType[2]].includes('llistat-temes')) {
    taulaLlistatTemes();
  } else if ([pageType[1], pageType[2]].includes('llistat-links')) {
    taulaLlistatLinks();
  } else if ([pageType[1], pageType[2]].includes('tema')) {
    taulaLlistatAdrecesPerTema();
  }
}
