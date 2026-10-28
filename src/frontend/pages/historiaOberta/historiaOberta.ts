import { getPageType } from '../../utils/urlPath';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { renderHistoriaObertaList } from './llistatHistoriaObertaPaged';
import { taulaLlistatCursosHistoria } from './llistatCursos';
import { taulaArticlesCurs } from './fitxaCurs';
import { formCursArticle } from './formCursArticle';
import { initCronologia } from './cronologia/llistatEsdeveniments';
import { renderCursosArticlesList } from './llistatCursosArticles';
import { formCursHistoria } from './formCurs';
import { taulaLlistatOrganitzacions } from './llistatOrganitzacions';
import { formOrganitzacioHistoria } from './formOrganitzacio';
import { fitxaOrganitzacio } from './fitxaOrganitzacio';
import { taulaLlistatEsdeveniments } from './llistatEsdeveniments';
import { formEsdevenimentHistoria } from './formEsdeveniment';
import { fitxaEsdeveniment } from './fitxaEsdeveniment';

const url = window.location.href;
const pageType = getPageType(url);

export function historiaOberta() {
  if (pageType[2] === 'llistat-articles') {
    renderHistoriaObertaList();
  }

  if (pageType[2] === 'llistat-cursos') {
    taulaLlistatCursosHistoria();
  }

  if (pageType[2] === 'llistat-slots-cursos-articles') {
    renderCursosArticlesList();
  }

  if (pageType[2] === 'fitxa-curs') {
    const id = pageType[3] ?? '';
    void taulaArticlesCurs(id);
  }

  if (pageType[2] === 'modifica-curs-article') {
    const id = pageType[3];
    void formCursArticle(true, id);
  }

  if (pageType[2] === 'nou-curs-article') {
    void formCursArticle(false);
  }

  if (pageType[2] === 'cronologia-esdeveniments') {
    void initCronologia();
  }

  if (pageType[2] === 'llistat-esdeveniments') {
    taulaLlistatEsdeveniments();
  }

  if (pageType[3] === 'modifica-article') {
    //'/api/historia/get/?carrecsPersona=', data.id, ['Càrrec', 'Organització', 'Anys', 'Accions'], function (fila, columna) {
    // /api/historia/get/?esdevenimentsPersona=', data.id, ['Esdeveniment', 'Any', 'Accions'], function (fila, columna) {
  }

  if (pageType[2] === 'nou-esdeveniment') {
    formEsdevenimentHistoria(false);
  } else if (pageType[2] === 'modifica-esdeveniment') {
    const esde = pageType[3];
    formEsdevenimentHistoria(true, esde);
  }

  if (pageType[2] === 'fitxa-esdeveniment') {
    const esde = pageType[3];
    fitxaEsdeveniment('historia/get/esdevenimentSlug', esde);
  }

  if (pageType[2] === 'nou-persona-carrec') {
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
  } else if (pageType[2] === 'llistat-organitzacions') {
    taulaLlistatOrganitzacions();
  } else if (pageType[2] === 'modifica-organitzacio') {
    const org = pageType[3];
    formOrganitzacioHistoria(true, org);
  } else if (pageType[2] === 'nova-organitzacio') {
    formOrganitzacioHistoria(false);
  }

  if (pageType[2] === 'nou-curs') {
    formCursHistoria(false);
  }

  if (pageType[2] === 'fitxa-organitzacio') {
    const slug = pageType[3];
    fitxaOrganitzacio('historia/get/organitzacioSlug', slug);
  }

  if (pageType[2] === 'modifica-curs') {
    const slug = pageType[3];
    formCursHistoria(true, slug);
  }
}
