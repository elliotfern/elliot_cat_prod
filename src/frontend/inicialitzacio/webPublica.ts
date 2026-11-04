import { getCoursesList } from '../pages/historiaOberta/cursosWebPublica';
import { historiaWebPublica } from '../pages/historiaOberta/historiaWebPublica';

import { renderBlogListPaged } from '../pages/blog/llistatArticles';
import { renderBlogArticleView } from '../pages/blog/article';

import { loginPage } from '../pages/login/funcions';
import { formRegistreUsuari } from '../pages/login/registreUsuari';
import { bancDadesWebPublica } from '../pages/banc-dades/bancDades';

export function webPublica(pageType: string[]): void {
  // Pàgina principal
  if (pageType.length === 0) {
    getCoursesList();
    return;
  }

  // Login
  if (pageType[0] === 'entrada') {
    loginPage();
    return;
  }

  // Registre d'usuaris
  if (pageType[0] === 'nou-usuari') {
    formRegistreUsuari();
    return;
  }

  // Història oberta
  if (pageType[0] === 'historia') {
    historiaWebPublica();
    return;
  }

  // Banc de dades
  if (pageType[0] === 'banc-dades') {
    bancDadesWebPublica(pageType);
    return;
  }

  // Blog
  if (pageType[0] === 'blog') {
    if (pageType.length === 1) {
      renderBlogListPaged();
      return;
    }

    if (pageType.length === 2) {
      renderBlogArticleView(pageType[1], 'blog');
    }
  }
}
