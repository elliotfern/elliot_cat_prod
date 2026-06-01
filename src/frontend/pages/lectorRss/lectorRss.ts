import { getPageType } from '../../utils/urlPath';
import { feeds, lectorFeeds } from './lectorFeeds';

const url = window.location.href;
const pageType = getPageType(url);

export function lectorRss() {
  if ([pageType[1], pageType[0]].includes('lector-rss')) {
    feeds.forEach((feed: { buttonId: string; url: string }) => {
      const button = document.getElementById(feed.buttonId);
      if (button) {
        button.addEventListener('click', () => {
          lectorFeeds(feed.url, 'feed');
        });
      }
    });

    const btnBlogs = document.getElementById('btnBlogs');
    const btnMedios = document.getElementById('btnMedios');
    const grupoBlogs = document.getElementById('grupoBlogs');
    const grupoMedios = document.getElementById('grupoMedios');

    if (btnBlogs && grupoBlogs && grupoMedios) {
      btnBlogs.addEventListener('click', () => {
        grupoBlogs.classList.toggle('hidden');
        grupoMedios.classList.add('hidden');
      });
    }

    if (btnMedios && grupoMedios && grupoBlogs) {
      btnMedios.addEventListener('click', () => {
        grupoMedios.classList.toggle('hidden');
        grupoBlogs.classList.add('hidden');
      });
    }
  }
}
