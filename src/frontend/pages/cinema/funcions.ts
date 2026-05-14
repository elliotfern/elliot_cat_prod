import { getPageType } from '../../utils/urlPath';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { taulaLlistatPelicules } from './taulaLlistatPelicules';
import { fitxaPelicula } from './fitxaPelicula';
import { fitxaSerie } from './fitxaSerie';
import { formSerie } from './formSerie';

export async function cinema() {
  const url = window.location.href;
  const pageType = getPageType(url);

  const slug = pageType[2];
  const idSlug = pageType[3];

  if (slug === 'modifica-pelicula') {
  } else if (pageType[2] === 'nova-pelicula') {
  } else if (pageType[2] === 'modifica-serie') {
    formSerie(true, idSlug);
  } else if (pageType[2] === 'nova-serie') {
    formSerie(false);
  } else if (pageType[2] === 'inserir-actor-pelicula') {
    // transmissioDadesDB(event, 'POST', 'inserirActorPelicula', '/api/cinema/post/?actorPelicula');
  } else if (pageType[2] === 'modifica-actor-pelicula') {
    // transmissioDadesDB(event, 'PUT', 'inserirActorPelicula', '/api/cinema/put/?actorPelicula');
  } else if (slug === 'llistat-pelicules') {
    taulaLlistatPelicules();
  } else if (slug === 'fitxa-pelicula') {
    const url = 'https://elliot.cat/api/cinema/get/pelicula?peliSlug=';
    fitxaPelicula(url, idSlug);
  } else if (slug === 'fitxa-serie') {
    const url = 'https://elliot.cat/api/cinema/get/serie?serieSlug=';
    fitxaSerie(url, idSlug);
  }
}
