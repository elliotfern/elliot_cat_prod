import { getPageType } from '../../utils/urlPath';
import { formProjecte } from './formProjecte';
import { initProjectesHome } from './homeProjectes';

const url = window.location.href;
const pageType = getPageType(url);

export function projectes() {
  const id = parseInt(pageType[3], 10);

  if (pageType[1] !== 'projectes') return;

  if (pageType.length === 2) {
    // /projectes
    void initProjectesHome();
    return;
  }

  switch (pageType[2]) {
    case 'modifica-projecte':
      formProjecte(true, id);
      break;

    case 'crea-projecte':
      formProjecte(false);
      break;

    default:
      void initProjectesHome();
  }
}
