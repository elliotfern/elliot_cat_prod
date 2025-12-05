import { getPageType } from '../../utils/urlPath';
import { initCalendariAgenda } from './agendaCalendari';
import { carregarEsdevenimentDetall } from './agendaEsdevenimentId';
import { carregarAgendaFutura } from './agendaEsdevenimentsFuturs';

const url = window.location.href;
const pageType = getPageType(url);

export function agenda() {
  const id = parseInt(pageType[3], 10);
  const idLocale = parseInt(pageType[4], 10);

  if (pageType[2] === 'llistat-esdeveniments') {
    const usuariId = 1;
    carregarAgendaFutura(usuariId);
  } else if (pageType[2] === 'calendari-esdeveniments') {
    initCalendariAgenda();
  } else if (pageType[2] === 'veure-esdeveniment') {
    carregarEsdevenimentDetall();
  }
}
