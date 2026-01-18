import { getPageType } from '../../utils/urlPath';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { fitxaPersona } from '../../pages/persona/fitxaPersona';
import { construirTaula } from '../../services/api/construirTaula';
import { taulaLlistatAutors } from './taulaLlistatAutors';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { taulaLlistatLlibres } from './taulaLlistatLlibres';
import { fetchApiDataLlibre } from './fitxaLlibre';
import { initAdminButtons, initLlibreAutorsPage } from './fitxaLlibreAutors';

const url = window.location.href;
const pageType = getPageType(url);

export async function biblioteca() {
  if (pageType[2] === 'modifica-llibre') {
    const llibre = document.getElementById('modificaLlibre');
    if (llibre) {
      // Lanzar actualizador de datos
      llibre.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'PUT', 'modificaLlibre', '/api/biblioteca/put/?llibre');
      });
    }
  } else if (pageType[2] === 'nou-llibre') {
    const llibre = document.getElementById('modificaLlibre');
    if (llibre) {
      // Lanzar actualizador de datos
      llibre.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'POST', 'modificaLlibre', '/api/biblioteca/post/?llibre');
      });
    }
  } else if ([pageType[1], pageType[2]].includes('fitxa-autor')) {
    const isAdmin = await getIsAdmin();
    const url = window.location.href;
    const pageType = getPageType(url);
    let slug: string = '';

    if (isAdmin) {
      slug = pageType[3];
    } else {
      slug = pageType[2];
    }

    fitxaPersona('/api/persones/get/?persona=', slug, 'biblioteca-autor', function (data) {
      construirTaula('taula1', '/api/biblioteca/get/?type=autorLlibres&id=', data.id, ['Titol', 'Any', 'Accions'], function (fila, columna) {
        if (columna.toLowerCase() === 'titol') {
          // Manejar el caso del título
          return '<a href="' + window.location.origin + '/gestio/biblioteca/fitxa-llibre/' + fila['slug'] + '">' + fila['titol'] + '</a>';
        } else if (columna.toLowerCase() === 'accions') {
          return `<button onclick="window.location.href='${window.location.origin}/gestio/biblioteca/modifica-llibre/${fila['slug']}'" class="button btn-petit">Modificar</button>`;
        } else {
          // Manejar otros casos
          return fila[columna.toLowerCase()];
        }
      });
    });
  } else if ([pageType[1], pageType[2]].includes('llistat-autors')) {
    taulaLlistatAutors();
  } else if ([pageType[1], pageType[2]].includes('llistat-llibres')) {
    taulaLlistatLlibres();
  } else if (pageType[2] === 'fitxa-llibre') {
    const slug = pageType[3];
    fetchApiDataLlibre(`/api/biblioteca/get/?llibreSlug=${slug}`);
  } else if (pageType[2] === 'fitxa-llibre-autors') {
    const slug = pageType[3];
    initLlibreAutorsPage(slug);
    initAdminButtons(slug);
  }
}
