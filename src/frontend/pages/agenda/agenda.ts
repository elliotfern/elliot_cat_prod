import { getPageType } from '../../utils/urlPath';
import { carregarAgendaFutura } from './agendaEsdevenimentsFuturs';

const url = window.location.href;
const pageType = getPageType(url);

export function agenda() {
  const id = parseInt(pageType[3], 10);
  const idLocale = parseInt(pageType[4], 10);

  if (pageType[2] === 'llistat-esdeveniments') {
    const usuariId = 1;
    carregarAgendaFutura(usuariId);
  }
}
