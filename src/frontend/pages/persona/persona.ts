import { getPageType } from '../../utils/urlPath';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { taulaLlistatPersones } from './taulaLlistatPersones';
import { fitxaPersona } from './fitxaPersona';
import { construirTaula } from '../../services/api/construirTaula';
import { selectOmplirDades } from '../../components/lecturaDadesForm/selectOmplirDades';
import { formPersona } from './formPersona';

const url = window.location.href;
const pageType = getPageType(url);

export function persona() {
  const slug = pageType[3];

  if (pageType[2] === 'modifica-persona') {
    formPersona(true, slug);
  } else if (pageType[2] === 'fitxa-persona') {
    //fitxaPersona('https://api.elliot.cat/api/persona/', pageType[3], 'persona', function (data) {});
    fitxaPersona('/api/persones/get/?persona="', pageType[3], 'persona', function (data) {});
  } else if (pageType[2] === 'nova-persona') {
    formPersona(false);
  } else if ([pageType[1], pageType[0]].includes('base-dades-persones')) {
    taulaLlistatPersones();
  }
}
