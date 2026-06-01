import { getPageType } from '../../utils/urlPath';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { taulaLlistatPelicules } from './taulaLlistatPelicules';
import { fitxaPelicula } from './fitxaPelicula';
import { fitxaSerie } from './fitxaSerie';
import { formSerie } from './formSerie';
import { formPelicula } from './formPelicula';

export async function cinema() {
  const url = window.location.href;
  const pageType = getPageType(url);

  const slug = pageType[2];
  const idSlug = pageType[3];

  if (slug === 'modifica-pelicula') {
    formPelicula(true, idSlug);
  } else if (pageType[2] === 'nova-pelicula') {
    formPelicula(false);
  } else if (pageType[2] === 'modifica-serie') {
    formSerie(true, idSlug);
  } else if (pageType[2] === 'nova-serie') {
    formSerie(false);
  } else if (slug === 'llistat-pelicules') {
    taulaLlistatPelicules();
  } else if (slug === 'fitxa-pelicula') {
    const url = 'cinema/get/pelicula?peliSlug=';
    fitxaPelicula(url, idSlug);
  } else if (slug === 'fitxa-serie') {
    const url = 'cinema/get/serie';
    fitxaSerie(url, idSlug);
  }
}
