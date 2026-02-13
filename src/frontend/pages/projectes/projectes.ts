import { getPageType } from '../../utils/urlPath';
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
  const id = idRaw ? Number.parseInt(String(idRaw), 10) : undefined;

  // /.../projectes
  if (!action) {
    void initProjectesHome();
    return;
  }

  switch (action) {
    case 'modifica-projecte':
      formProjecte(true, id);
      break;

    case 'nou-projecte':
      formProjecte(false);
      break;

    case 'nova-tasca':
      console.log('hola');
      formTask(false);
      break;

    case 'modifica-tasca':
      formTask(true, id);
      break;

    default:
      void initProjectesHome();
  }
}
