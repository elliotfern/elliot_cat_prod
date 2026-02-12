import { getPageType } from '../../utils/urlPath';
import { formProjecte } from './formProjecte';
import { initProjectesHome } from './homeProjectes';

const url = window.location.href;
const pageType = getPageType(url);

export function projectes() {
  const id = parseInt(pageType[3], 10);
  const idLocale = parseInt(pageType[4], 10);

  if (pageType[1] === 'projectes') {
    void initProjectesHome();
  } else if (pageType[2] === 'modifica-projecte') {
    formProjecte(true, id);
  } else if (pageType[2] === 'crea-projecte') {
    formProjecte(false);
  }
}
