import { getIsAdmin } from '../../services/auth/isAdmin';
import { getPageType } from '../../utils/urlPath';
import { renderBlogArticleView } from './article';
import { formBlogArticle } from './formArticle';
import { renderBlogListPaged } from './llistatArticles';

export async function blog() {
  const pageType = getPageType(window.location.href);

  // ✅ Óptimo: calculamos 1 sola vez
  const isAdmin = await getIsAdmin();
  let slug = '';
  if (isAdmin) {
    slug = pageType[3];
  } else {
    slug = pageType[2];
  }

  // Encuentra dónde está "blog" en la ruta
  const iBlog = pageType.indexOf('blog');
  if (iBlog === -1) return;

  const actionRaw = pageType[iBlog + 1] ?? '';
  const action = String(actionRaw).split('?')[0].replace(/\/+$/, '');

  const idRaw = pageType[iBlog + 2];
  const id = Number.parseInt(String(idRaw), 10);

  // /.../blog
  if (!action) {
    void renderBlogListPaged();
    return;
  }

  switch (action) {
    case 'article':
      void renderBlogArticleView(slug, 'blog');
      break;

    case 'modifica-article':
      void formBlogArticle(true, id);
      break;

    case 'nou-article':
      formBlogArticle(false);
      break;

    default:
  }
}
