import 'trix/dist/trix.css';
import 'trix';
import './estils/style.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

import { getPageType } from './utils/urlPath';
import { cinema } from './pages/cinema/funcions';
import { loginPage } from './pages/login/funcions';
import { vault } from './pages/vault/funcions';
import { historiaOberta } from './pages/historiaOberta/historiaOberta';
import { biblioteca } from './pages/biblioteca/biblioteca';
import { adreces } from './pages/adreces/adreces';
import { persona } from './pages/persona/persona';
import { viatges } from './pages/viatges/viatges';
import { comptabilitat } from './pages/comptabilitat/comptabilitat';
import { barraNavegacio } from './components/barraNavegacio/barraNavegacio';
import { mostrarBotonsNomesAdmin } from './components/mostrarBotons/mostrarBoton';
import { auxiliars } from './pages/auxiliars/auxiliars';
import { logout } from './services/login/logOutApi';
import { contactes } from './pages/contactes/contactes';
import { lectorRss } from './pages/lectorRss/lectorRss';
import { usuaris } from './pages/gestioUsuaris/usuaris';
import { areaPrivadaUsuaris } from './pages/areaPrivadaUsuaris/funcions';
import { transmissioDadesDB } from './utils/actualitzarDades';
import { curriculum } from './pages/curriculum/curriculum';
import { agenda } from './pages/agenda/agenda';
import { projectes } from './pages/projectes/projectes';
import { initUserAreaButton } from './components/header/userAreaButton';
import { blog } from './pages/blog/blog';
import { initI18nHeaderLinks } from './components/header/i18nHeaderLinks';

function whenElementExists(id: string, cb: () => void, timeoutMs = 4000): void {
  if (document.getElementById(id)) {
    cb();
    return;
  }

  const obs = new MutationObserver(() => {
    if (document.getElementById(id)) {
      obs.disconnect();
      cb();
    }
  });

  obs.observe(document.documentElement, { childList: true, subtree: true });

  window.setTimeout(() => obs.disconnect(), timeoutMs);
}

document.addEventListener('trix-before-initialize', function () {
  // H2
  Trix.config.blockAttributes.heading2 = {
    tagName: 'h2',
    terminal: true,
    breakOnReturn: true,
    group: false,
  };

  // H3
  Trix.config.blockAttributes.heading3 = {
    tagName: 'h3',
    terminal: true,
    breakOnReturn: true,
    group: false,
  };

  // H4
  Trix.config.blockAttributes.heading4 = {
    tagName: 'h4',
    terminal: true,
    breakOnReturn: true,
    group: false,
  };
});

document.addEventListener('trix-initialize', function (event) {
  const editorElement = event.target as any;
  const toolbar = editorElement.toolbarElement;
  if (!toolbar) return;

  const blockGroup = toolbar.querySelector('.trix-button-group--block-tools');
  if (!blockGroup) return;

  const customGroup = document.createElement('span');
  customGroup.className = 'trix-button-group';

  customGroup.innerHTML = `
    <button type="button" class="trix-button" data-trix-attribute="heading2">H2</button>
    <button type="button" class="trix-button" data-trix-attribute="heading3">H3</button>
    <button type="button" class="trix-button" data-trix-attribute="heading4">H4</button>
  `;

  blockGroup.appendChild(customGroup);
});

document.addEventListener('DOMContentLoaded', () => {
  const url = window.location.href;
  const pageType = getPageType(url);

  initI18nHeaderLinks();
  void initUserAreaButton();
  barraNavegacio();
  mostrarBotonsNomesAdmin();

  const logoutButton = document.getElementById('logoutButton');
  if (logoutButton) {
    logoutButton.addEventListener('click', logout);
  }

  console.log(pageType);
  if (pageType[1] === 'entrada') {
    loginPage();
  } else if (pageType[0] === 'nou-usuari') {
    const autor = document.getElementById('formUsuari');
    if (autor) {
      // Lanzar actualizador de datos
      autor.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'POST', 'formUsuari', '/api/auth/post/usuari');
      });
    }
  } else if (pageType[1] === 'claus-privades') {
    vault();
  } else if (pageType[1] === 'comptabilitat') {
    comptabilitat();
  } else if (pageType[1] === 'auxiliars') {
    auxiliars();
  } else if (pageType[1] === 'agenda-contactes') {
    contactes();
  } else if (pageType[1] === 'curriculum') {
    curriculum();
  } else if (pageType[1] === 'agenda') {
    agenda();
  } else if (pageType.includes('projectes')) {
    // si es una pantalla que inyecta forms tarde, espera al contenedor
    whenElementExists('taskForm', () => projectes(), 6000);
    // y por si es la home o nou-projecte (sin taskForm), también:
    whenElementExists('formProjecte', () => projectes(), 6000);
    // fallback: llama una vez igualmente
    projectes();
    // Part accessible tant a usuaris com a visitants
  } else if (pageType[1] === 'lector-rss' || pageType[0] === 'lector-rss') {
    lectorRss();
  } else if (pageType[1] === 'historia' || pageType[0] === 'historia') {
    historiaOberta();
  } else if (pageType[1] === 'biblioteca' || pageType[0] === 'biblioteca') {
    biblioteca();
  } else if (pageType[1] === 'adreces' || pageType[0] === 'adreces') {
    adreces();
  } else if (pageType[1] === 'base-dades-persones' || pageType[0] === 'base-dades-persones') {
    persona();
  } else if (pageType[1] === 'viatges' || pageType[0] === 'viatges') {
    viatges();
  } else if (pageType[1] === 'cinema' || pageType[0] === 'cinema') {
    cinema();
  } else if (pageType[1] === 'gestio-usuaris') {
    usuaris();
  } else if (pageType[0] === 'usuaris') {
    areaPrivadaUsuaris();
  } else if (pageType[1] === 'blog' || pageType[0] === 'blog') {
    blog();
  }
});
