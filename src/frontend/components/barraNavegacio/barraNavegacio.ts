import { DOMAIN_WEB, INTRANET_WEB } from '../../utils/urls';

// Capitalitzar paraules
function capitalizeWords(str: string): string {
  return str
    .replace(/-/g, ' ')
    .split(' ')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(' ');
}

export async function barraNavegacio(): Promise<void> {
  const url = new URL(window.location.href);
  let parts = url.pathname.split('/').filter(Boolean);

  const inGestio = parts[0] === 'gestio';

  // Eliminar prefix estructural de gestió
  if (inGestio) {
    parts = parts.slice(1);
  }

  // Filtrar parts que no han d'aparèixer al breadcrumb
  const filteredParts = parts.filter((part, index) => {
    if (part.includes('fitxa')) return false;

    if (!inGestio && part === 'article' && parts[index - 1] === 'blog') {
      return false;
    }

    return true;
  });

  // Construcció breadcrumb
  const nav = document.createElement('nav');
  nav.className = 'breadcrumb mb-3 p-2 rounded bg-light border';
  nav.setAttribute('aria-label', 'breadcrumb');

  const ol = document.createElement('ol');
  ol.className = 'breadcrumb mb-0';

  // Inici
  const homeLi = document.createElement('li');
  homeLi.className = 'breadcrumb-item';

  const homeLink = document.createElement('a');

  if (inGestio) {
    homeLink.href = INTRANET_WEB;
    homeLink.textContent = 'Intranet';
  } else {
    homeLink.href = DOMAIN_WEB;
    homeLink.textContent = 'Inici';
  }

  homeLi.appendChild(homeLink);
  ol.appendChild(homeLi);

  // Breadcrumb dinàmic
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

      const fullPath = inGestio ? `/gestio/${accumPath}` : `/${accumPath}`;

      link.href = new URL(fullPath, inGestio ? INTRANET_WEB : DOMAIN_WEB).toString();
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
