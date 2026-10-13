export function decorateLinksInHtml(html: string): string {
  const doc = new DOMParser().parseFromString(html, 'text/html');

  // Eliminar scripts del contenido recibido.
  doc.querySelectorAll('script').forEach((node) => node.remove());

  // 1. Convertir URL en texto plano en enlaces.
  const urlRegex = /https?:\/\/[^\s<]+/gi;

  function linkifyTextNode(node: Text): void {
    const text = node.nodeValue ?? '';

    // Evitar que el estado global de la expresión regular
    // provoque resultados inconsistentes entre llamadas a test().
    urlRegex.lastIndex = 0;

    if (!urlRegex.test(text)) return;

    urlRegex.lastIndex = 0;

    const fragment = doc.createDocumentFragment();
    let lastIndex = 0;

    for (const match of text.matchAll(urlRegex)) {
      const matchedUrl = match[0];
      const index = match.index;

      // Conservar la puntuación final fuera del enlace.
      const cleanUrl = matchedUrl.replace(/[.,;!?]+$/, '');
      const trailingPunctuation = matchedUrl.slice(cleanUrl.length);

      if (index > lastIndex) {
        fragment.append(doc.createTextNode(text.slice(lastIndex, index)));
      }

      if (cleanUrl) {
        const anchor = doc.createElement('a');
        anchor.href = cleanUrl;
        anchor.textContent = cleanUrl;
        fragment.append(anchor);
      }

      if (trailingPunctuation) {
        fragment.append(doc.createTextNode(trailingPunctuation));
      }

      lastIndex = index + matchedUrl.length;
    }

    if (lastIndex < text.length) {
      fragment.append(doc.createTextNode(text.slice(lastIndex)));
    }

    node.replaceWith(fragment);
  }

  const walker = doc.createTreeWalker(doc.body, NodeFilter.SHOW_TEXT);
  const textNodes: Text[] = [];

  while (walker.nextNode()) {
    const current = walker.currentNode as Text;

    // No modificar texto que ya está dentro de un enlace,
    // ni código o elementos que no debamos procesar.
    if (current.parentElement?.closest('a, script, style, code, pre, textarea')) {
      continue;
    }

    textNodes.push(current);
  }

  textNodes.forEach(linkifyTextNode);

  // 2. Decorar los enlaces internos y externos.
  const anchors = Array.from(doc.querySelectorAll<HTMLAnchorElement>('a[href]'));

  for (const anchor of anchors) {
    const href = anchor.getAttribute('href')?.trim() ?? '';

    if (!href) continue;

    let url: URL;

    try {
      url = new URL(href, window.location.origin);
    } catch {
      continue;
    }

    // Solo decorar enlaces HTTP/HTTPS.
    const isHttp = url.protocol === 'http:' || url.protocol === 'https:';

    if (!isHttp) continue;

    const hostname = url.hostname.toLowerCase();

    const isInternal = hostname === 'elliot.cat' || hostname.endsWith('.elliot.cat') || hostname === 'elliot.local' || hostname.endsWith('.elliot.local');

    const isExternal = !isInternal;

    if (isExternal) {
      anchor.target = '_blank';
      anchor.rel = 'noopener noreferrer';
      anchor.classList.add('link-external');
    } else {
      anchor.removeAttribute('target');
      anchor.removeAttribute('rel');
      anchor.classList.add('link-internal');
    }

    // Eliminar espacios al final del texto visible del enlace.
    for (const anchor of anchors) {
      for (const node of Array.from(anchor.childNodes).reverse()) {
        if (node.nodeType !== Node.TEXT_NODE) break;

        const textNode = node as Text;
        const trimmed = textNode.nodeValue?.replace(/\s+$/, '') ?? '';

        if (trimmed) {
          textNode.nodeValue = trimmed;
          break;
        }

        textNode.remove();
      }
    }

    // Añadir el icono solo una vez.
    if (anchor.querySelector('.link-external-icon')) continue;

    if (isExternal && !anchor.querySelector('.link-external-icon')) {
      const icon = doc.createElement('span');

      icon.className = 'link-external-icon';
      icon.setAttribute('aria-hidden', 'true');
      icon.innerHTML = `
    <svg
      xmlns="http://www.w3.org/2000/svg"
      width="0.8em"
      height="0.8em"
      viewBox="0 0 16 16"
      fill="none"
      stroke="currentColor"
      stroke-width="1.5"
      stroke-linecap="round"
      stroke-linejoin="round"
      style="vertical-align: baseline;"
    >
      <path d="M6 3H3.5A1.5 1.5 0 0 0 2 4.5v8A1.5 1.5 0 0 0 3.5 14h8a1.5 1.5 0 0 0 1.5-1.5V10"/>
      <path d="M9 2h5v5"/>
      <path d="M14 2 7 9"/>
    </svg>
  `;

      anchor.append(icon);
    }
  }

  return doc.body.innerHTML;
}
