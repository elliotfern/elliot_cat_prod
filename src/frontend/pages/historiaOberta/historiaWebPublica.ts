import { getPageType } from '../../utils/urlPath';
import { getCursHistoria } from './fitxaCursWebPublica';
import { renderArticle } from './fitxaArticleWebPublica';
import { getCoursesList } from './cursosWebPublica';

const url = window.location.href;
const pageType = getPageType(url);

export function historiaWebPublica() {
  if (pageType[1] === 'curs') {
    const curs = pageType[2];
    getCursHistoria(curs);
  }

  if (pageType[1] === 'article') {
    const slug = pageType[2];
    renderArticle(slug);
  }

  if (pageType[0] === 'historia') {
    getCoursesList();
    return;
  }
}
