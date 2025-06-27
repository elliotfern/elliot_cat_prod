import { getPageType } from '../../utils/urlPath';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { taulaLlistatPersones } from './taulaLlistatPersones';
import { fitxaPersona } from './fitxaPersona';

const url = window.location.href;
const pageType = getPageType(url);

export function persona() {
  if (pageType[2] === 'modifica-persona') {
    const autor = document.getElementById('modificaAutor');
    if (autor) {
      // Lanzar actualizador de datos
      autor.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'PUT', 'modificaAutor', '/api/biblioteca/put/?autor');
      });
    }
  } else if (pageType[2] === 'fitxa-persona') {
    fitxaPersona('/api/persones/get/?persona=', pageType[3], 'persona', function (data) {
      /* construirTaula('taula1', '/api/cinema/get/actor-pelicules?slug=', data.slug, ['Titol', 'Any', 'Rol'], function (fila, columna) {
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
        transmissioDadesDB(event, 'POST', 'modificaAutor', '/api/biblioteca/post/?autor');
      });
    }
  } else if ([pageType[1], pageType[0]].includes('base-dades-persones')) {
    taulaLlistatPersones();
  }
}
