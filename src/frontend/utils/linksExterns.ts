export function decorateLinksInHtml(html: string): string {
  const doc = new DOMParser().parseFromString(html ?? '', 'text/html');

  // Seguridad básica
  doc.querySelectorAll('script').forEach((n) => n.remove());

  const anchors = Array.from(doc.querySelectorAll<HTMLAnchorElement>('a[href]'));

  for (const a of anchors) {
    const rawHref = a.getAttribute('href') ?? '';
    const href = rawHref.trim();
    if (!href) continue;

    let url: URL | null = null;
    try {
      url = new URL(href, window.location.origin);
    } catch {
      url = null;
    }
    if (!url) continue;

    const isHttp = url.protocol === 'http:' || url.protocol === 'https:';
    if (!isHttp) continue; // ignoramos mailto:, tel:, etc.

    const isElliotCat = url.hostname === 'elliot.cat' || url.hostname.endsWith('.elliot.cat');

    const isExternal = !isElliotCat;

    // 🔥 Si es externo → abre en nueva pestaña
    if (isExternal) {
      a.target = '_blank';
      a.rel = 'noopener noreferrer';
    } else {
      // Si es interno → aseguramos que NO tenga target
      a.removeAttribute('target');
      a.removeAttribute('rel');
    }

    // Evitar duplicados
    if (a.dataset.extDecorated === '1') continue;
    a.dataset.extDecorated = '1';

    // Añadir icono Bootstrap
    a.insertAdjacentHTML('beforeend', ` <i class="bi bi-box-arrow-up-right ms-1" aria-hidden="true"></i>`);

    // Clase opcional para estilo
    a.classList.add(isExternal ? 'link-external' : 'link-internal');
  }

  return doc.body.innerHTML;
}
