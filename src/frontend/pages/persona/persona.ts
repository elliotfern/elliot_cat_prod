import { getPageType } from '../../utils/urlPath';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { taulaLlistatPersones } from './taulaLlistatPersones';
import { fitxaPersona } from './fitxaPersona';
import { construirTaula } from '../../services/api/construirTaula';

const url = window.location.href;
const pageType = getPageType(url);

export function persona() {
  if (pageType[2] === 'modifica-persona') {
    const autor = document.getElementById('modificaAutor');
    if (autor) {
      // Lanzar actualizador de datos
      autor.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'PUT', 'modificaAutor', 'https://api.elliot.cat/api/persona/');
      });
    }
  } else if (pageType[2] === 'fitxa-persona') {
    fitxaPersona('https://api.elliot.cat/api/persona/', pageType[3], 'persona', function (data) {
      /* if (data.grups.includes('0197b088-1a25-72c4-8b5b-d7e2ee27de7c')) {
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
      }
       construirTaula('taula1', '/api/cinema/get/actor-pelicules?slug=', data.slug, ['Titol', 'Any', 'Rol'], function (fila, columna) {
           if (columna.toLowerCase() === 'titol') {
             // Manejar el caso del título
             return `<a href="https://${window.location.host}/gestio/cinema/fitxa-pelicula/${fila['slug']}">${fila['titol']}</a>`;
           } else if (columna.toLowerCase() === 'any') {
             return `${fila['anyInici']}${fila['anyFi'] ? ' - ' + fila['anyFi'] : ''}`;
           } else if (columna.toLowerCase() === 'rol') {
             // Manejar otros casos
             return `${fila['role']}`;
           } else {
             // Manejar otros casos
             return fila[columna.toLowerCase()];
           }*/
    });
  } else if (pageType[2] === 'nova-persona') {
    const autor = document.getElementById('modificaAutor');
    if (autor) {
      // Lanzar actualizador de datos
      autor.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'POST', 'modificaAutor', 'https://api.elliot.cat/api/persona/');
      });
    }
  } else if ([pageType[1], pageType[0]].includes('base-dades-persones')) {
    taulaLlistatPersones();
  }
}
