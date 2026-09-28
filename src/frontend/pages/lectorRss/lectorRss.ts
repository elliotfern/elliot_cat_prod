import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { getPageType } from '../../utils/urlPath';
import { lectorFeeds } from './lectorFeeds';

interface FeedRss {
  id: string;
  nom: string;
  web: string;
  feed: string;
  tipus: string;
  sub_tema_id: string;
  ordre: number;
  sub_tema: string | null;
  tema: string | null;
}

type VarianteBoto = 'primary' | 'secondary' | 'dark';

const ENDPOINT_FEEDS = 'rss/get/llistatFeeds';
const ID_CONTENIDOR_FEED = 'feed';
const SENSE_SUBTEMA = 'Sense subtema';

const TIPUS: { tipus: string; titol: string }[] = [
  { tipus: 'blog', titol: 'Blogs' },
  { tipus: 'mitja', titol: 'Mitjans' },
];

function crearBoto(text: string, variant: VarianteBoto, title?: string): HTMLButtonElement {
  const boto = document.createElement('button');
  boto.type = 'button';
  boto.className = `btn btn-outline-${variant}`;
  boto.textContent = text;
  if (title) boto.title = title;
  return boto;
}

function marcarActiu(contenidor: HTMLElement, actiu: HTMLElement | null): void {
  contenidor.querySelectorAll('button').forEach((b) => {
    const esActiu = b === actiu;
    b.classList.toggle('active', esActiu);
    b.setAttribute('aria-pressed', String(esActiu));
  });
}

function agruparPerSubtema(feeds: FeedRss[]): Map<string, FeedRss[]> {
  const grups = new Map<string, FeedRss[]>();
  const ordenats = [...feeds].sort((a, b) => a.ordre - b.ordre || a.nom.localeCompare(b.nom));

  for (const feed of ordenats) {
    const clau = feed.sub_tema ?? SENSE_SUBTEMA;
    const llista = grups.get(clau) ?? [];
    llista.push(feed);
    grups.set(clau, llista);
  }
  return grups;
}

function renderitzarLector(contenidor: HTMLElement, feeds: FeedRss[]): void {
  contenidor.replaceChildren();

  const nivell1 = document.createElement('div');
  const nivell2 = document.createElement('div');
  const nivell3 = document.createElement('div');
  const zonaLectura = document.createElement('div');

  [nivell1, nivell2, nivell3].forEach((n) => {
    n.className = 'd-flex flex-wrap gap-2 mb-3';
  });
  zonaLectura.id = ID_CONTENIDOR_FEED;

  const mostrarFeeds = (feedsSubtema: FeedRss[]): void => {
    nivell3.replaceChildren();
    for (const feed of feedsSubtema) {
      const boto = crearBoto(feed.nom, 'dark', feed.web);
      boto.addEventListener('click', () => {
        marcarActiu(nivell3, boto);
        lectorFeeds(feed.feed, ID_CONTENIDOR_FEED);
      });
      nivell3.appendChild(boto);
    }
  };

  const mostrarSubtemes = (tipus: string): void => {
    nivell2.replaceChildren();
    nivell3.replaceChildren();

    const grups = agruparPerSubtema(feeds.filter((f) => f.tipus === tipus));

    grups.forEach((feedsSubtema, subtema) => {
      const boto = crearBoto(subtema, 'secondary');
      boto.addEventListener('click', () => {
        marcarActiu(nivell2, boto);
        mostrarFeeds(feedsSubtema);
      });
      nivell2.appendChild(boto);
    });
  };

  for (const { tipus, titol } of TIPUS) {
    if (!feeds.some((f) => f.tipus === tipus)) continue;

    const boto = crearBoto(titol, 'primary');
    boto.addEventListener('click', () => {
      marcarActiu(nivell1, boto);
      mostrarSubtemes(tipus);
    });
    nivell1.appendChild(boto);
  }

  contenidor.append(nivell1, nivell2, nivell3, zonaLectura);
}

export async function lectorRss(): Promise<void> {
  const pageType = getPageType(window.location.href);
  if (![pageType[1], pageType[0]].includes('lector-rss')) return;

  const contenidor = document.getElementById('lectorRSS');
  if (!contenidor) return;

  try {
    const feeds = await api.get<FeedRss[]>(ENDPOINT_FEEDS);
    renderitzarLector(contenidor, feeds);
  } catch (error) {
    console.error(error);
    contenidor.textContent = "No s'han pogut carregar els feeds RSS.";
  }
}
