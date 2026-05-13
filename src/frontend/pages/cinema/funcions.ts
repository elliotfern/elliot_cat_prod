import { getPageType } from '../../utils/urlPath';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { connexioApiDades } from '../../components/lecturaDadesForm/mostrarDades/connexioApiDades';
import { fitxaPersona } from '../persona/fitxaPersona';
import { construirTaula } from '../../services/api/construirTaula';
import { taulaLlistatPelicules } from './taulaLlistatPelicules';
import { getIsAdmin } from '../../services/auth/isAdmin';

export async function cinema() {
  const url = window.location.href;
  const pageType = getPageType(url);

  const isAdmin = await getIsAdmin();

  let slug: string = '';
  let gestioUrl: string = '';

  if (isAdmin) {
    slug = pageType[3];
    gestioUrl = '/gestio';
  } else {
    slug = pageType[2];
  }

  if (pageType[2] === 'modifica-pelicula') {
    const peli = document.getElementById('peli');
    if (peli) {
      peli.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'PUT', 'peli', '/api/cinema/put/?type=pelicula');
      });
    }
  } else if ([pageType[1], pageType[2]].includes('fitxa-pelicula')) {
    connexioApiDades('/api/cinema/get/pelicula?peliSlug=', slug, 'img', 'cinema-pelicula', function (data) {
      // Actualiza el atributo href del enlace con el idDirector
      const directorUrl = document.getElementById('directorUrl') as HTMLAnchorElement;
      if (directorUrl) {
        directorUrl.href = `${window.location.origin}/gestio/base-dades-persones/fitxa-persona/${data.slugDirector}`;
      }
    });

    // author book
    // llistatPeliculaActors(pageType[3]);
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
  } else if (pageType[2] === 'fitxa-actor') {
    fitxaPersona('/api/persones/get/?persona=', pageType[3], 'cinema-actor', function (data) {
      construirTaula('taula1', '/api/cinema/get/actor-pelicules?slug=', data.slug, ['Titol', 'Any', 'Rol'], function (fila, columna) {
        if (columna.toLowerCase() === 'titol') {
          // Manejar el caso del título
          return `<a href="https://${window.location.host}/gestio/cinema/fitxa-pelicula/${fila['slug']}">${fila['titol']}</a>`;
        } else if (columna.toLowerCase() === 'any') {
          return `${fila['anyInici']}${fila['anyFi'] ? ' - ' + fila['anyFi'] : ''}`;
        } else if (columna.toLowerCase() === 'rol') {
          // Manejar otros casos
          return `${fila['role']}`;
        } else {
          // Manejar otros casos
          return fila[columna.toLowerCase()];
        }
      });
      construirTaula('taula2', '/api/cinema/get/actor-series?slug=', data.slug, ['Titol', 'Any', 'Rol'], function (fila, columna) {
        if (columna.toLowerCase() === 'titol') {
          // Manejar el caso del título
          return `<a href="https://${window.location.host}/gestio/cinema/fitxa-serie/${fila['slug']}">${fila['titol']}</a>`;
        } else if (columna.toLowerCase() === 'any') {
          return `${fila['anyInici']}${fila['anyFi'] ? ' - ' + fila['anyFi'] : ''}`;
        } else if (columna.toLowerCase() === 'rol') {
          // Manejar otros casos
          return `${fila['role']}`;
        } else {
          // Manejar otros casos
          return fila[columna.toLowerCase()];
        }
      });
    });
  } else if (pageType[2] === 'fitxa-director') {
    fitxaPersona('/api/persones/get/?persona=', pageType[3], 'cinema-director', function (data) {
      construirTaula('taula1', '/api/cinema/get/directorPelicules?id=', data.id, ['', 'Titol', 'Any', 'Gènere'], function (fila, columna) {
        if (columna.toLowerCase() === '') {
          // Manejar el caso del título
          return `<a id="pelicula-${fila['id']}" title="pelicula" href="${window.location.origin}/gestio/cinema/fitxa-pelicula/${fila['slug']}">
                        <img src="https://media.elliot.cat/img/cinema-pelicula/${fila['nameImg']}.jpg" width="100" height="auto">
                    </a>`;
        } else if (columna.toLowerCase() === 'titol') {
          // Manejar el caso del título
          return `<a href="https://${window.location.host}/gestio/cinema/fitxa-pelicula/${fila['slug']}">${fila['name']}</a>`;
        } else if (columna.toLowerCase() === 'any') {
          return `${fila['anyInici']}${fila['anyFi'] ? ' - ' + fila['anyFi'] : ''}`;
        } else if (columna.toLowerCase() === 'gènere') {
          // Manejar otros casos
          return `${fila['genere_ca']}`;
        } else {
          // Manejar otros casos
          return fila[columna.toLowerCase()];
        }
      });
      construirTaula('taula2', '/api/cinema/get/directorSeries?id=', data.id, ['', 'Titol', 'Any', 'Gènere'], function (fila, columna) {
        if (columna.toLowerCase() === '') {
          // Manejar el caso del título
          return `<a id="serie-${fila['id']}" title="serie" href="${window.location.origin}/gestio/cinema/fitxa-serie/${fila['slug']}">
                        <img src="https://media.elliot.cat/img/cinema-serie/${fila['nameImg']}.jpg" width="100" height="auto">
                    </a>`;
        } else if (columna.toLowerCase() === 'titol') {
          // Manejar el caso del título
          return `<a href="https://${window.location.host}/gestio/cinema/fitxa-serie/${fila['slug']}">${fila['name']}</a>`;
        } else if (columna.toLowerCase() === 'any') {
          return `${fila['anyInici']}${fila['anyFi'] ? ' - ' + fila['anyFi'] : ''}`;
        } else if (columna.toLowerCase() === 'gènere') {
          // Manejar otros casos
          return `${fila['genere_ca']}`;
        } else {
          // Manejar otros casos
          return fila[columna.toLowerCase()];
        }
      });
    });
  } else if ([pageType[1], pageType[2]].includes('llistat-pelicules')) {
    taulaLlistatPelicules();
  }
}
