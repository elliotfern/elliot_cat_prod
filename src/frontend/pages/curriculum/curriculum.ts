import { getPageType } from '../../utils/urlPath';
import { formPerfil } from './formPerfil';

const url = window.location.href;
const pageType = getPageType(url);

export function curriculum() {
  if (pageType[2] === 'nou-perfil') {
    formPerfil(false);
  } else if (pageType[2] === 'modifica-perfil') {
    const id = parseInt(pageType[3], 10);
    formPerfil(true, id);
  }
}
