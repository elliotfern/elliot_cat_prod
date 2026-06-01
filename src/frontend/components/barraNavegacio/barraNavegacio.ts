import { getIsAdmin } from '../../services/auth/isAdmin';
import { isLang } from '../../utils/locales/getLangPrefix';
import { DOMAIN_WEB, INTRANET_WEB } from '../../utils/urls';

// Capitalizar
function capitalizeWords(str: string) {
  return str
    .replace(/-/g, ' ')
    .split(' ')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(' ');
}

export async function barraNavegacio() {
  const isAdmin = await getIsAdmin();

  const url = new URL(window.location.href);
  let parts = url.pathname.split('/').filter(Boolean);

  const inGestio = parts[0] === 'gestio';

  // 🔧 Normalizar partes (quitamos prefijos estructurales)
  if (inGestio) {
    parts = parts.slice(1); // quitar "gestio"
  }

  let langPrefix = '';

  if (!inGestio && parts.length && isLang(parts[0])) {
    langPrefix = `/${parts[0]}`;
    parts = parts.slice(1); // quitar idioma
  }

  // Base URL limpia
  const baseUrl = inGestio ? INTRANET_WEB : DOMAIN_WEB;

  // Filtrado de partes
  const filteredParts = parts.filter((p, index) => {
    if (p.includes('fitxa')) return false;
    if (!inGestio && p === 'article' && parts[index - 1] === 'blog') return false;
    return true;
  });

  // Construcción breadcrumb
  const nav = document.createElement('nav');
  nav.className = 'breadcrumb mb-3 p-2 rounded bg-light border';
  nav.setAttribute('aria-label', 'breadcrumb');

  const ol = document.createElement('ol');
  ol.className = 'breadcrumb mb-0';

  // 🏠 Home
  const homeLi = document.createElement('li');
  homeLi.className = 'breadcrumb-item';

  const homeLink = document.createElement('a');

  if (inGestio) {
    homeLink.href = INTRANET_WEB;
    homeLink.textContent = 'Intranet';
  } else {
    homeLink.href = `${DOMAIN_WEB}${langPrefix}/homepage`;
    homeLink.textContent = 'Inici';
  }

  homeLi.appendChild(homeLink);
  ol.appendChild(homeLi);

  // 🔗 Breadcrumb dinámico
  filteredParts.forEach((part, index) => {
    const li = document.createElement('li');
    const label = capitalizeWords(part);
    const isLast = index === filteredParts.length - 1;

    li.className = 'breadcrumb-item';

    if (isLast) {
      li.classList.add('active');
      li.setAttribute('aria-current', 'page');
      li.textContent = label;
    } else {
      const link = document.createElement('a');

      const accumPath = filteredParts.slice(0, index + 1).join('/');

      const fullPath = inGestio ? `/gestio/${accumPath}` : `${langPrefix}/${accumPath}`;

      link.href = new URL(fullPath, baseUrl).toString();
      link.textContent = label;

      li.appendChild(link);
    }

    ol.appendChild(li);
  });

  nav.appendChild(ol);

  // Render
  const container = document.getElementById('barraNavegacioContenidor');
  if (container) {
    container.innerHTML = '';
    container.appendChild(nav);
  }
}
