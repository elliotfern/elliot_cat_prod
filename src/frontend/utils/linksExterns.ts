export function decorateLinksInHtml(html: string): string {
  const doc = new DOMParser().parseFromString(html ?? '', 'text/html');

  doc.querySelectorAll('script').forEach((n) => n.remove());

  // 1️⃣ Convertir URLs en texto plano en <a>
  const urlRegex = /(https?:\/\/[^\s<]+)/gi;

  function linkifyTextNode(node: Text) {
    const text = node.nodeValue ?? '';
    if (!urlRegex.test(text)) return;

    const span = doc.createElement('span');
    span.innerHTML = text.replace(urlRegex, (url) => {
      const cleanUrl = url.replace(/[.,;!?]+$/, ''); // limpia puntuación final
      return `<a href="${cleanUrl}">${cleanUrl}</a>`;
    });

    node.replaceWith(...Array.from(span.childNodes));
  }

  const walker = doc.createTreeWalker(doc.body, NodeFilter.SHOW_TEXT);
  const textNodes: Text[] = [];
  while (walker.nextNode()) {
    const current = walker.currentNode as Text;
    if (current.parentElement?.closest('a')) continue; // evita duplicar dentro de <a>
    textNodes.push(current);
  }
  textNodes.forEach(linkifyTextNode);

  // 2️⃣ Decorar todos los <a>
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
    if (!isHttp) continue;

    const isElliotCat = url.hostname === 'elliot.cat' || url.hostname.endsWith('.elliot.cat');

    const isExternal = !isElliotCat;

    if (isExternal) {
      a.target = '_blank';
      a.rel = 'noopener noreferrer';
    } else {
      a.removeAttribute('target');
      a.removeAttribute('rel');
    }

    if (a.dataset.linkDecorated === '1') continue;
    a.dataset.linkDecorated = '1';

    a.classList.add(isExternal ? 'link-external' : 'link-internal');

    a.insertAdjacentHTML('beforeend', ` <i class="bi bi-box-arrow-up-right ms-1" aria-hidden="true"></i>`);
  }

  return doc.body.innerHTML;
}
