import { getPageType } from '../../utils/urlPath';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { taulaLlistatAutors } from './taulaLlistatAutors';
import { taulaLlistatLlibres } from './taulaLlistatLlibres';
import { fitxaLlibre } from './fitxaLlibre';
import { initAdminButtons, initLlibreAutorsPage } from './fitxaLlibreAutors';
import { taulaLlistatGrups } from './taulaLlistatGrups';
import { formLlibre } from './formLlibre';

const url = window.location.href;
const pageType = getPageType(url);

export async function biblioteca() {
  if (pageType[2] === 'modifica-llibre') {
    const id = pageType[3];
    formLlibre(true, id);
  } else if (pageType[2] === 'nou-llibre') {
    formLlibre(false);
  } else if ([pageType[1], pageType[2]].includes('llistat-autors')) {
    taulaLlistatAutors();
  } else if ([pageType[1], pageType[2]].includes('llistat-llibres')) {
    taulaLlistatLlibres();
  } else if ([pageType[1], pageType[2]].includes('llistat-grups')) {
    taulaLlistatGrups();
  } else if (pageType[2] === 'fitxa-llibre') {
    const slug = pageType[3];
    fitxaLlibre(`biblioteca/get/llibreSlug`, slug);
  } else if (pageType[2] === 'fitxa-llibre-autors') {
    const slug = pageType[3];
    initLlibreAutorsPage(slug);
    initAdminButtons(slug);
  } else if (pageType[2] === 'nou-grup') {
    const llibre = document.getElementById('formAfegirGrup');
    if (llibre) {
      // Lanzar actualizador de datos
      llibre.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'POST', 'formAfegirGrup', '/api/biblioteca/post/?grupLlibre');
      });
    }
  }
}
