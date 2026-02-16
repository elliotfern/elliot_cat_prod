import { getPageType } from '../../utils/urlPath';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { fitxaPersona } from '../persona/fitxaPersona';
import { construirTaula } from '../../services/api/construirTaula';
import { renderBlogArticleView } from '../blog/article';

const url = window.location.href;
const pageType = getPageType(url);

function isLang(seg: string | undefined): boolean {
  return ['ca', 'es', 'en', 'fr', 'it', 'pt'].includes(String(seg || '').toLowerCase());
}

export function historiaOberta() {
  // /ca/historia/article/<slug>
  if (pageType[2] === 'article') {
    const slug = pageType[3] ?? '';
    const langPrefix = isLang(pageType[0]) ? `/${pageType[0]}` : '';
    // const backHref = `${langPrefix}/historia`;

    void renderBlogArticleView(slug, 'historia');
  }

  if (pageType[3] === 'modifica-article') {
  } else if (pageType[2] === 'fitxa-persona') {
    fitxaPersona('/api/persones/get/?persona=', pageType[3], 'historia-persona', function (data) {
      construirTaula('taula1', '/api/historia/get/?carrecsPersona=', data.id, ['Càrrec', 'Organització', 'Anys', 'Accions'], function (fila, columna) {
        if (columna.toLowerCase() === 'càrrec') {
          // Manejar el caso del título
          return fila['carrec'];
        } else if (columna.toLowerCase() === 'organització') {
          return '<a href="' + window.location.origin + '/gestio/historia/fitxa-organitzacio/' + fila['slug'] + '">' + fila['organitzacio'] + '</a>';
        } else if (columna.toLowerCase() === 'anys') {
          return `${fila['anys']} / ${fila['carrecFi']}`;
        } else if (columna.toLowerCase() === 'accions') {
          return `<button onclick="window.location.href='${window.location.origin}/gestio/historia/modifica-persona-carrec/${fila['id']}'" class="button btn-petit">Modificar</button>`;
        } else {
          // Manejar otros casos
          return fila[columna.toLowerCase()];
        }
      });

      construirTaula('taula2', '/api/historia/get/?esdevenimentsPersona=', data.id, ['Esdeveniment', 'Any', 'Accions'], function (fila, columna) {
        if (columna.toLowerCase() === 'esdeveniment') {
          // Manejar el caso del título
          return '<a href="' + window.location.origin + '/gestio/historia/fitxa-esdeveniment/' + fila['slug'] + '">' + fila['esdeveniment'] + '</a>';
        } else if (columna.toLowerCase() === 'anys') {
          return `${fila['esdeDataIAny']}`;
        } else if (columna.toLowerCase() === 'accions') {
          return `<button onclick="window.location.href='${window.location.origin}/gestio/historia/modifica-esdeveniment-persona/${fila['idEP']}'" class="button btn-petit">Modificar</button>`;
        } else {
          // Manejar otros casos
          return fila[columna.toLowerCase()];
        }
      });
    });
  } else if (pageType[2] === 'nou-esdeveniment') {
    const form = document.getElementById('formEsdeveniment');
    if (form) {
      // Lanzar actualizador de datos
      form.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'POST', 'formEsdeveniment', '/api/historia/post/?esdeveniment');
      });
    }
  } else if (pageType[2] === 'modifica-esdeveniment') {
    const form = document.getElementById('formEsdeveniment');
    if (form) {
      // Lanzar actualizador de datos
      form.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'PUT', 'formEsdeveniment', '/api/historia/put/?esdeveniment');
      });
    }
  } else if (pageType[2] === 'modifica-esdeveniment-persona') {
    const form = document.getElementById('formEsdeveniment');

    if (form) {
      form.addEventListener('submit', function (event) {
        event.preventDefault();

        const submitter = event.submitter; // El botón que activó el envío
        const metodo = submitter?.dataset?.method || 'POST'; // Valor por defecto: POST

        transmissioDadesDB(event, metodo, 'formEsdeveniment', `/api/historia/${metodo.toLowerCase()}/?esdevenimentPersona`);
      });
    }
  } else if (pageType[2] === 'modifica-esdeveniment-organitzacio') {
    const form = document.getElementById('formEsdeveniment');

    if (form) {
      form.addEventListener('submit', function (event) {
        event.preventDefault();

        const submitter = event.submitter; // El botón que activó el envío
        const metodo = submitter?.dataset?.method || 'POST'; // Valor por defecto: POST

        transmissioDadesDB(event, metodo, 'formEsdeveniment', `/api/historia/${metodo.toLowerCase()}/?esdevenimentOrganitzacio`);
      });
    }
  } else if (pageType[2] === 'nou-persona-carrec') {
    const form = document.getElementById('formPersonaCarrec');
    if (form) {
      // Lanzar actualizador de datos
      form.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'POST', 'formPersonaCarrec', '/api/historia/post/?personaCarrec');
      });
    }
  } else if (pageType[2] === 'modifica-persona-carrec') {
    const form = document.getElementById('formPersonaCarrec');
    if (form) {
      // Lanzar actualizador de datos
      form.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'PUT', 'formPersonaCarrec', '/api/historia/put/?personaCarrec');
      });
    }
  } else if (pageType[2] === 'modifica-organitzacio') {
    const form = document.getElementById('formOrganitzacio');
    if (form) {
      // Lanzar actualizador de datos
      form.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'PUT', 'formOrganitzacio', '/api/historia/put/?organitzacio');
      });
    }
  } else if (pageType[2] === 'nova-organitzacio') {
    const form = document.getElementById('formOrganitzacio');
    if (form) {
      // Lanzar actualizador de datos
      form.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'POST', 'formOrganitzacio', '/api/historia/post/?organitzacio');
      });
    }
  }
}
