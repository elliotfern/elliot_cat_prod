import { getPageType } from '../../utils/urlPath';
import { taulaUsuaris } from './taulaUsuaris';
import { transmissioDadesDB } from '../../utils/actualitzarDades';

const url = window.location.href;
const pageType = getPageType(url);

export function usuaris() {
  if (pageType[2] === 'llistat-usuaris') {
    taulaUsuaris();
  } else if (pageType[2] === 'modifica-usuari') {
    const autor = document.getElementById('formUsuari');
    if (autor) {
      // Lanzar actualizador de datos
      autor.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'PUT', 'formUsuari', '/api/auth/put/usuari');
      });
    }
  } else if (pageType[2] === 'nou-usuari') {
    const autor = document.getElementById('formUsuari');
    if (autor) {
      // Lanzar actualizador de datos
      autor.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'POST', 'formUsuari', '/api/auth/post/usuari');
      });
    }
  }
}
