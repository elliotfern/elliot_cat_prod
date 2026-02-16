import { getIsAdmin } from '../../services/auth/isAdmin';
import { isLang } from '../../utils/locales/getLangPrefix';
import { DOMAIN_WEB, INTRANET_WEB } from '../../utils/urls';

// Función para capitalizar la primera letra de cada palabra
function capitalizeWords(str: string) {
  return str
    .replace(/-/g, ' ')
    .split(' ')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(' ');
}

export async function barraNavegacio() {
  const isAdmin = await getIsAdmin();

  const baseAdminUrl = INTRANET_WEB;
  const baseUserUrl = DOMAIN_WEB;

  // ✅ Partimos de pathname (sin dominio), más fiable
  let parts = window.location.pathname.split('/').filter(Boolean);

  // ✅ Detectar si realmente estamos en /gestio (aunque isAdmin sea true)
  const inGestio = parts[0] === 'gestio';

  // ✅ En público: quitar el prefijo de idioma si existe
  // Ej: /ca/historia/article/foo -> quitamos "ca"
  if (!inGestio && isLang(parts[0])) {
    parts = parts.slice(1);
  }

  // Base para construir links
  const baseUrl = inGestio ? baseAdminUrl : baseUserUrl;

  // ✅ En público, si hay idioma, lo conservamos como prefijo en las URLs del breadcrumb
  // Ej: /ca/historia/... => prefix="/ca"
  const langPrefix = !inGestio && isLang(window.location.pathname.split('/').filter(Boolean)[0]) ? `/${window.location.pathname.split('/').filter(Boolean)[0]}` : '';

  // ✅ Contenedor con estilo Bootstrap
  let breadcrumbHtml = `
    <nav class="breadcrumb mb-3 p-2 rounded bg-light border" aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
  `;

  // Primer item
  if (inGestio) {
    breadcrumbHtml += `<li class="breadcrumb-item"><a href="${baseAdminUrl}">Intranet</a></li>`;
  } else {
    // en público, el "inicio" debería respetar idioma (si existe)
    const homeHref = `${baseUserUrl}${langPrefix}/homepage`;
    breadcrumbHtml += `<li class="breadcrumb-item"><a href="${homeHref}">Inici</a></li>`;
  }

  // Items restantes
  const filteredParts = parts.filter((p, index) => {
    // eliminar "fitxa"
    if (p.includes('fitxa')) return false;

    // eliminar "article" cuando viene después de blog
    if (!inGestio && p === 'article' && parts[index - 1] === 'blog') {
      return false;
    }

    return true;
  });

  filteredParts.forEach((part, index) => {
    const label = capitalizeWords(part);

    // Construimos URL acumulada:
    // - gestio: https://elliot.cat/gestio/...
    // - público: https://elliot.cat/{lang}/...
    const accum = filteredParts.slice(0, index + 1).join('/');
    const href = inGestio ? `${baseUrl}/${accum}` : `${baseUrl}${langPrefix}/${accum}`;

    const isLast = index === filteredParts.length - 1;

    if (isLast) {
      breadcrumbHtml += `<li class="breadcrumb-item active" aria-current="page">${label}</li>`;
    } else {
      breadcrumbHtml += `<li class="breadcrumb-item"><a href="${href}">${label}</a></li>`;
    }
  });

  breadcrumbHtml += `
      </ol>
    </nav>
  `;

  const elementHTML = document.getElementById('barraNavegacioContenidor');
  if (elementHTML) {
    elementHTML.innerHTML = breadcrumbHtml;
  }
}
