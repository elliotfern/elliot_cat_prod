import { getPageType } from '../../utils/urlPath';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { taulaLlistatPelicules } from './taulaLlistatPelicules';
import { fitxaPelicula } from './fitxaPelicula';
import { fitxaSerie } from './fitxaSerie';

export async function cinema() {
  const url = window.location.href;
  const pageType = getPageType(url);

  const slug = pageType[2];
  const idSlug = pageType[3];

  if (slug === 'modifica-pelicula') {
    // transmissioDadesDB(event, 'PUT', 'peli', '/api/cinema/put/?type=pelicula');
  } else if (pageType[2] === 'modifica-serie') {
    //transmissioDadesDB(event, 'PUT', 'modificarSerie', '/api/cinema/put/serie');
  } else if (pageType[2] === 'nova-serie') {
    //transmissioDadesDB(event, 'POST', 'modificarSerie', '/api/cinema/post/serie');
  } else if (pageType[2] === 'modifica-pelicula') {
    //transmissioDadesDB(event, 'PUT', 'modificarPeli', '/api/cinema/put/?pelicula');
  } else if (pageType[2] === 'nova-pelicula') {
    //transmissioDadesDB(event, 'POST', 'modificarPeli', '/api/cinema/post/?pelicula');
  } else if (pageType[2] === 'inserir-actor-pelicula') {
    // transmissioDadesDB(event, 'POST', 'inserirActorPelicula', '/api/cinema/post/?actorPelicula');
  } else if (pageType[2] === 'modifica-actor-pelicula') {
    // transmissioDadesDB(event, 'PUT', 'inserirActorPelicula', '/api/cinema/put/?actorPelicula');
  } else if (pageType[2] === 'inserir-actor-serie') {
    //  transmissioDadesDB(event, 'POST', 'inserirActorSerie', '/api/cinema/post/?actorSerie');
  } else if (pageType[2] === 'modifica-actor-serie') {
    //  transmissioDadesDB(event, 'PUT', 'inserirActorSerie', '/api/cinema/put/?actorSerie');
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
