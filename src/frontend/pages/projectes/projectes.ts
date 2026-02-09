import { getPageType } from '../../utils/urlPath';
import { initProjectesHome } from './homeProjectes';

const url = window.location.href;
const pageType = getPageType(url);

export function projectes() {
  const id = parseInt(pageType[3], 10);
  const idLocale = parseInt(pageType[4], 10);

  if (pageType[1] === 'projectes') {
    initProjectesHome();
  } else if (pageType[2] === 'calendari-es') {
    //initCalendariAgenda();
  }
}
