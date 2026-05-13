import { getPageType } from '../../utils/urlPath';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { taulaLlistatPelicules } from './taulaLlistatPelicules';
import { fitxaPelicula } from './fitxaPelicula';

export async function cinema() {
  const url = window.location.href;
  const pageType = getPageType(url);

  const slug = pageType[2];
  const idSlug = pageType[3];

  if (slug === 'modifica-pelicula') {
    const peli = document.getElementById('peli');
    if (peli) {
      peli.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'PUT', 'peli', '/api/cinema/put/?type=pelicula');
      });
    }
  } else if (slug === 'fitxa-pelicula') {
    fitxaPelicula(idSlug);
  } else if (pageType[2] === 'modifica-serie') {
    const serie = document.getElementById('modificarSerie');
    if (serie) {
      // Lanzar actualizador de datos
      serie.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'PUT', 'modificarSerie', '/api/cinema/put/?serie');
      });
    }
  } else if (pageType[2] === 'nova-serie') {
    const serie = document.getElementById('modificarSerie');
    if (serie) {
      // Lanzar actualizador de datos
      serie.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'POST', 'modificarSerie', '/api/cinema/post/?serie');
      });
    }
  } else if (pageType[2] === 'modifica-pelicula') {
    const serie = document.getElementById('modificarPeli');
    if (serie) {
      // Lanzar actualizador de datos
      serie.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'PUT', 'modificarPeli', '/api/cinema/put/?pelicula');
      });
    }
  } else if (pageType[2] === 'nova-pelicula') {
    const serie = document.getElementById('modificarPeli');
    if (serie) {
      // Lanzar actualizador de datos
      serie.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'POST', 'modificarPeli', '/api/cinema/post/?pelicula');
      });
    }
  } else if (pageType[2] === 'inserir-actor-pelicula') {
    const serie = document.getElementById('inserirActorPelicula');
    if (serie) {
      // Lanzar actualizador de datos
      serie.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'POST', 'inserirActorPelicula', '/api/cinema/post/?actorPelicula');
      });
    }
  } else if (pageType[2] === 'modifica-actor-pelicula') {
    const serie = document.getElementById('inserirActorPelicula');
    if (serie) {
      // Lanzar actualizador de datos
      serie.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'PUT', 'inserirActorPelicula', '/api/cinema/put/?actorPelicula');
      });
    }
  } else if (pageType[2] === 'inserir-actor-serie') {
    const serie = document.getElementById('inserirActorSerie');
    if (serie) {
      // Lanzar actualizador de datos
      serie.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'POST', 'inserirActorSerie', '/api/cinema/post/?actorSerie');
      });
    }
  } else if (pageType[2] === 'modifica-actor-serie') {
    const serie = document.getElementById('inserirActorSerie');
    if (serie) {
      // Lanzar actualizador de datos
      serie.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'PUT', 'inserirActorSerie', '/api/cinema/put/?actorSerie');
      });
    }
  } else if (slug === 'llistat-pelicules') {
    taulaLlistatPelicules();
  }
}
