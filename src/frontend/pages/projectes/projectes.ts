import { getPageType } from '../../utils/urlPath';
import { initProjecteDetalls } from './fitxaProjecte';
import { formProjecte } from './formProjecte';
import { formTask } from './formTasca';
import { initProjectesHome } from './homeProjectes';

export function projectes() {
  const pageType = getPageType(window.location.href);

  // Encuentra dónde está "projectes" en la ruta
  const iProjectes = pageType.indexOf('projectes');
  if (iProjectes === -1) return;

  const actionRaw = pageType[iProjectes + 1] ?? '';
  const action = String(actionRaw).split('?')[0].replace(/\/+$/, '');

  const idRaw = pageType[iProjectes + 2];
  const id = Number.parseInt(String(idRaw), 10);

  // /.../projectes
  if (!action) {
    void initProjectesHome();
    return;
  }

  switch (action) {
    case 'modifica-projecte':
      void formProjecte(true, id);
      break;

    case 'nou-projecte':
      void formProjecte(false);
      break;

    case 'nova-tasca':
      console.log('hola');
      void formTask(false);
      break;

    case 'modifica-tasca':
      void formTask(true, id);
      break;

    case 'fitxa-projecte':
      void initProjecteDetalls(id);
      break;

    default:
      void initProjectesHome();
  }
}
