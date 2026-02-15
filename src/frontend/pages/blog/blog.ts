import { getPageType } from '../../utils/urlPath';
import { renderLlistatArticlesBlog } from './llistatArticles';

export function blog() {
  const pageType = getPageType(window.location.href);

  // Encuentra dónde está "blog" en la ruta
  const iBlog = pageType.indexOf('blog');
  if (iBlog === -1) return;

  const actionRaw = pageType[iBlog + 1] ?? '';
  const action = String(actionRaw).split('?')[0].replace(/\/+$/, '');

  const idRaw = pageType[iBlog + 2];
  const id = Number.parseInt(String(idRaw), 10);

  // /.../blog
  if (!action) {
    void renderLlistatArticlesBlog();
    return;
  }

  switch (action) {
    case 'modifica-projecte':
      break;

    case 'nou-projecte':
      break;

    case 'nova-tasca':
      break;

    case 'modifica-tasca':
      break;

    case 'fitxa-projecte':
      break;

    default:
  }
}
