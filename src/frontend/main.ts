import 'trix/dist/trix.css';
import 'trix';
import './assets/css/style.css';

import { getPageType } from './utils/urlPath';
import { loginPage } from './pages/login/funcions';
import { barraNavegacio } from './components/barraNavegacio/barraNavegacio';
import { mostrarBotonsNomesAdmin } from './components/mostrarBotons/mostrarBoton';
import { logout } from './services/login/logOutApi';
import { transmissioDadesDB } from './utils/actualitzarDades';
import { initUserAreaButton } from './components/header/userAreaButton';
import { getCoursesList } from './pages/historiaOberta/cursosWebPublica';
import { intranet_admin } from './pages/intranet_admin/intranet_admin';
import { historiaWebPublica } from './pages/historiaOberta/historiaWebPublica';
import { renderBlogListPaged } from './pages/blog/llistatArticles';
import { renderBlogArticleView } from './pages/blog/article';

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

async function main() {
  const url = window.location.href;
  const pageType = getPageType(url);
  console.log(pageType);

  void initUserAreaButton();
  barraNavegacio();
  mostrarBotonsNomesAdmin();

  const logoutButton = document.getElementById('logoutButton');
  if (logoutButton) {
    logoutButton.addEventListener('click', logout);
  }

  if (pageType.length === 0) {
    getCoursesList();
  }

  if (pageType[0] === 'gestio') {
    intranet_admin();
  }

  if (pageType[0] === 'entrada') {
    loginPage();
  } else if (pageType[0] === 'nou-usuari') {
    const autor = document.getElementById('formUsuari');
    if (autor) {
      // Lanzar actualizador de datos
      autor.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'POST', 'formUsuari', '/api/auth/post/usuari');
      });
    }
  }

  if (pageType[0] === 'historia') {
    historiaWebPublica();
  }

  if (pageType[0] === 'blog') {
    if (pageType.length === 1) {
      renderBlogListPaged();
    }

    if (pageType.length === 2) {
      const slug = pageType[1];
      renderBlogArticleView(slug, 'blog');
    }
  }
}
