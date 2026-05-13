import { getPageType } from '../../utils/urlPath';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { renderBlogArticleView } from '../blog/article';
import { renderHistoriaObertaListPaged } from './llistatHistoriaObertaPaged';
import { taulaLlistatCursosHistoria } from './llistatCursos';
import { taulaArticlesCurs } from './fitxaCurs';
import { formCursArticle } from './formCursArticle';
import { initCronologia } from './cronologia/llistatEsdeveniments';

const url = window.location.href;
const pageType = getPageType(url);

function isLang(seg: string | undefined): boolean {
  return ['ca', 'es', 'en', 'fr', 'it'].includes(String(seg || '').toLowerCase());
}

export function historiaOberta() {
  // /ca/historia/article/<slug>
  if (pageType[2] === 'article') {
    const slug = pageType[3] ?? '';
    const langPrefix = isLang(pageType[0]) ? `/${pageType[0]}` : '';
    // const backHref = `${langPrefix}/historia`;

    void renderBlogArticleView(slug, 'historia');
  }

  if (pageType[2] === 'llistat-articles') {
    void renderHistoriaObertaListPaged();
  }

  if (pageType[2] === 'llistat-cursos') {
    void taulaLlistatCursosHistoria();
  }

  if (pageType[2] === 'fitxa-curs') {
    const id = pageType[3] ?? '';
    void taulaArticlesCurs(id);
  }

  if (pageType[2] === 'modifica-curs-article') {
    const id = Number(pageType[3]);
    void formCursArticle(true, id);
  }

  if (pageType[2] === 'nou-curs-article') {
    const id = pageType[3] ?? '';
    void formCursArticle(false);
  }

  if (pageType[2] === 'llistat-esdeveniments') {
    void initCronologia();
  }

  if (pageType[3] === 'modifica-article') {
  
     //'/api/historia/get/?carrecsPersona=', data.id, ['Càrrec', 'Organització', 'Anys', 'Accions'], function (fila, columna) {
     // /api/historia/get/?esdevenimentsPersona=', data.id, ['Esdeveniment', 'Any', 'Accions'], function (fila, columna) {
       
  } else if (pageType[2] === 'nou-esdeveniment') {
    const form = document.getElementById('formEsdeveniment');
    if (form) {
      // Lanzar actualizador de datos
      form.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'POST', 'formEsdeveniment', '/api/historia/post/esdeveniment');
      });
    }
  } else if (pageType[2] === 'modifica-esdeveniment') {
    const form = document.getElementById('formEsdeveniment');
    if (form) {
      // Lanzar actualizador de datos
      form.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'PUT', 'formEsdeveniment', '/api/historia/put/esdeveniment');
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
