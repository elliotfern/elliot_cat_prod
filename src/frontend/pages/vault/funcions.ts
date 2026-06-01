import { getPageType } from '../../utils/urlPath';
import { serveisVaultApi } from '../../components/vault/serveisVault';
import { transmissioDadesDB } from '../../utils/actualitzarDades';

const url = window.location.href;
const pageType = getPageType(url);

export function vault() {
  if (pageType[1] === 'claus-privades') {
    serveisVaultApi();
  }

  if (pageType[2] === 'modifica-vault') {
    const autor = document.getElementById('modificaVault');
    if (autor) {
      // Lanzar actualizador de datos
      autor.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'PUT', 'modificaVault', '/api/vault/put/?clau');
      });
    }
  } else if (pageType[2] === 'nou-vault') {
    const autor = document.getElementById('modificaVault');
    if (autor) {
      // Lanzar actualizador de datos
      autor.addEventListener('submit', function (event) {
        transmissioDadesDB(event, 'POST', 'modificaVault', '/api/vault/post/?clau');
      });
    }
  }
}
