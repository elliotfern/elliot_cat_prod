import { getPageType } from '../../utils/urlPath';
import { formPerfil } from './formPerfil';
import { formPerfilI18n } from './formPerfilI18n';
import { vistaPerfilCV } from './vistaPerfilCV';
import { vistaPerfilCVi18n } from './vistaPerfilCVi18n';

const url = window.location.href;
const pageType = getPageType(url);

export function curriculum() {
  const id = parseInt(pageType[3], 10);
  const idLocale = parseInt(pageType[4], 10);

  if (pageType[2] === 'nou-perfil') {
    formPerfil(false);
  } else if (pageType[2] === 'modifica-perfil') {
    formPerfil(true, id);
  } else if (pageType[2] === 'perfil-cv') {
    vistaPerfilCV();
  } else if (pageType[2] === 'nou-perfil-i18n') {
    formPerfilI18n(false);
  } else if (pageType[2] === 'modifica-perfil-i18n') {
    formPerfilI18n(true, idLocale);
  } else if (pageType[2] === 'perfil-cv-i18n') {
    vistaPerfilCVi18n();
  }
}
