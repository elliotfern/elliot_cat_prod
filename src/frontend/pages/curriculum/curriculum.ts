import { getPageType } from '../../utils/urlPath';
import { formExperiencies } from './formExperiencies';
import { formExperienciesI18n } from './formExperienciesI18n';
import { formHabilitats } from './formHabilitats';
import { formLink } from './formLink';
import { formPerfil } from './formPerfil';
import { formPerfilI18n } from './formPerfilI18n';
import { vistaExperienciaDetall } from './vistaExperienciaDetall';
import { vistaExperiencia } from './vistaExperiencies';
import { vistaHabilitats } from './vistaHabilitats';
import { vistaLinks } from './vistaLinks';
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
  } else if (pageType[2] === 'nou-link') {
    formLink(false);
  } else if (pageType[2] === 'modifica-link') {
    formLink(true, id);
  } else if (pageType[2] === 'perfil-links') {
    vistaLinks();
  } else if (pageType[2] === 'nova-habilitat') {
    formHabilitats(false);
  } else if (pageType[2] === 'modifica-habilitat') {
    formHabilitats(true, id);
  } else if (pageType[2] === 'perfil-habilitats') {
    vistaHabilitats();
  } else if (pageType[2] === 'nova-experiencia') {
    formExperiencies(false);
  } else if (pageType[2] === 'modifica-experiencia') {
    formExperiencies(true, id);
  } else if (pageType[2] === 'modifica-experiencia') {
    formExperiencies(true, id);
  } else if (pageType[2] === 'perfil-experiencies') {
    vistaExperiencia();
  } else if (pageType[2] === 'nova-experiencia-i18n') {
    formExperienciesI18n(false);
  } else if (pageType[2] === 'modifica-experiencia-i18n') {
    formExperienciesI18n(true, id);
  } else if (pageType[2] === 'perfil-experiencia-professional') {
    vistaExperienciaDetall(id);
  }
}
