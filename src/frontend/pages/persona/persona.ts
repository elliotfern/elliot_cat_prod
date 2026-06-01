import { getPageType } from '../../utils/urlPath';
import { taulaLlistatPersones } from './taulaLlistatPersones';
import { fitxaPersona } from './fitxaPersona';
import { formPersona } from './formPersona';

const url = window.location.href;
const pageType = getPageType(url);

export function persona() {
  const slug = pageType[3];

  if (pageType[2] === 'modifica-persona') {
    formPersona(true, slug);
  } else if (pageType[2] === 'fitxa-persona') {
    fitxaPersona('persones/get/persona', slug);
  } else if (pageType[2] === 'nova-persona') {
    formPersona(false);
  } else if ([pageType[1], pageType[0]].includes('base-dades-persones')) {
    taulaLlistatPersones();
  }
}
