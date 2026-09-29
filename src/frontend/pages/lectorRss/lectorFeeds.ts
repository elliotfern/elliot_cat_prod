interface FeedItem {
  title: string;
  link: string;
  date: string;
  description: string;
}

function htmlATexto(html: string): string {
  const doc = new DOMParser().parseFromString(html, 'text/html');
  doc.querySelectorAll('a.more-link').forEach((el) => el.remove());
  return (doc.body.textContent ?? '').replace(/\s+/g, ' ').trim();
}

function safeHttpUrl(raw: string): string | null {
  try {
    const parsed = new URL(raw);
    if (parsed.protocol === 'http:' || parsed.protocol === 'https:') {
      return parsed.toString();
    }
  } catch {
    // URL inválida
  }
  return null;
}

function renderizarItems(container: HTMLElement, items: FeedItem[]): void {
  const ul = document.createElement('ul');

  items.forEach((item) => {
    const li = document.createElement('li');

    const a = document.createElement('a');
    a.textContent = item.title;

    const href = item.link;
    const safeHref = safeHttpUrl(href);
    a.href = safeHref ?? '#';
    a.target = '_blank';
    a.rel = 'noopener noreferrer';

    const pFecha = document.createElement('p');
    const strong = document.createElement('strong');
    strong.textContent = item.date;
    pFecha.appendChild(strong);

    const pDesc = document.createElement('p');
    pDesc.textContent = htmlATexto(item.description);

    li.append(a, pFecha, pDesc);
    ul.appendChild(li);
  });

  container.replaceChildren(ul);
}

function procesarXML(xml: Document): FeedItem[] {
  const items = Array.from(xml.querySelectorAll('item'));
  return items.map((item) => {
    const title = item.querySelector('title')?.textContent || 'Sin título';
    const link = item.querySelector('link')?.textContent || '#';
    const description = item.querySelector('description')?.textContent || '';
    const pubDate = item.querySelector('pubDate')?.textContent || '';

    return {
      title,
      link,
      description,
      date: pubDate,
    };
  });
}

export function lectorFeeds(url: string, targetElement: string): void {
  const container = document.getElementById(targetElement);
  if (!container) return;

  container.innerHTML = '<p>Cargando...</p>';

  fetch(`/api/lector-rss/get/?url=${encodeURIComponent(url)}`)
    .then((response) => {
      if (!response.ok) throw new Error(`HTTP ${response.status}`);

      const contentType = response.headers.get('Content-Type');
      if (contentType && contentType.includes('application/json')) {
        return response.json();
      } else {
        return response.text();
      }
    })
    .then((data: unknown) => {
      let items: FeedItem[];

      if (typeof data === 'string') {
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(data, 'application/xml');
        if (xmlDoc.querySelector('parsererror')) {
          throw new Error('XML inválido');
        }
        items = procesarXML(xmlDoc);
      } else {
        if (!Array.isArray(data)) throw new Error('Respuesta inesperada');
        items = data as FeedItem[];
      }

      const container = document.getElementById(targetElement);
      if (container) renderizarItems(container, items);
    })
    .catch((error) => {
      console.error(`Error al obtener el feed: ${url}`, error);
      const container = document.getElementById(targetElement);
      if (container) {
        container.innerHTML = '<p>Error al cargar el feed.</p>';
      }
    });
}
