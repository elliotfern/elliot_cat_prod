interface FeedItem {
  title: string;
  link: string;
  date: string;
  description: string;
}

export const feeds = [
  {
    url: 'https://jaime.gomezobregon.com/feed',
    buttonId: 'btnFeed1',
    categoria: 'blogs',
  },
  {
    url: 'https://www.vilaweb.cat/feed/',
    buttonId: 'btnFeed2',
    categoria: 'medios',
  },
  {
    url: 'https://feeds.elpais.com/mrss-s/pages/ep/site/elpais.com/portada',
    buttonId: 'btnFeed3',
    categoria: 'medios',
  },
  {
    url: 'https://www.lavanguardia.com/rss/home.xml',
    buttonId: 'btnFeed4',
    categoria: 'medios',
  },
  {
    url: 'https://feeds.bbci.co.uk/news/world/rss.xml',
    buttonId: 'btnFeed5',
    categoria: 'medios',
  },
  {
    url: 'https://ara.cat/rss',
    buttonId: 'btnFeed6',
    categoria: 'medios',
  },

  {
    url: 'https://thecheis.com/feed/',
    buttonId: 'btnFeed7',
    categoria: 'blogs',
  },
];

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
      const contentType = response.headers.get('Content-Type');
      if (contentType && contentType.includes('application/json')) {
        return response.json();
      } else {
        return response.text();
      }
    })
    .then((data: any) => {
      if (typeof data === 'string') {
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(data, 'application/xml');
        data = procesarXML(xmlDoc); // Asegúrate de que procesarXML devuelva FeedItem[]
      }

      let html = '<ul>';
      (data as FeedItem[]).forEach((item) => {
        html += `
          <li>
              <a href="${item.link}" target="_blank">${item.title}</a>
              <p><strong>${item.date}</strong></p>
              <p>${item.description}</p>
          </li>`;
      });
      html += '</ul>';

      const container = document.getElementById(targetElement);
      if (container) {
        container.innerHTML = html;
      }
    })
    .catch((error) => {
      console.error(`Error al obtener el feed: ${url}`, error);
      const container = document.getElementById(targetElement);
      if (container) {
        container.innerHTML = '<p>Error al cargar el feed.</p>';
      }
    });
}
