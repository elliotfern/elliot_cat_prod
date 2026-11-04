import 'trix/dist/trix.css';
import 'trix';
import './assets/css/style.css';
import { getPageType } from './utils/urlPath';
import { barraNavegacio } from './components/barraNavegacio/barraNavegacio';
import { mostrarBotonsNomesAdmin } from './components/mostrarBotons/mostrarBoton';
import { logout } from './services/login/logOutApi';
import { initUserAreaButton } from './components/header/userAreaButton';
import { webUsuaris } from './inicialitzacio/webUsuaris';
import { webAdmin } from './inicialitzacio/webAdmin';
import { webPublica } from './inicialitzacio/webPublica';

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
  const editorElement = event.target as HTMLTrixEditorElement;
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
  main();
});

function main(): void {
  const pageType = getPageType(window.location.href);

  // Web publica
  void initUserAreaButton();
  barraNavegacio();
  mostrarBotonsNomesAdmin();

  const logoutButton = document.getElementById('logoutButton');
  if (logoutButton) {
    logoutButton.addEventListener('click', logout);
  }

  if (pageType[0] === 'gestio') {
    webAdmin(pageType);
    return;
  }

  if (pageType[0] === 'usuaris') {
    webUsuaris(pageType);
    return;
  }

  webPublica(pageType);
}
