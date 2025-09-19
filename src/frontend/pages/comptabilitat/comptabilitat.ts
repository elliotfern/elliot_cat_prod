import { getPageType } from '../../utils/urlPath';
import { formClient } from './formClient';
import { formFacturaClient } from './formFacturaClient';
import { taulaFacturacioClients } from './taulaFacturacioClients';
import { taulaLlistatClients } from './taulaLlistatClients';

export function comptabilitat() {
  const url = window.location.href;
  const pageType = getPageType(url);
  const id = parseInt(pageType[3], 10);

  if (pageType[2] === 'facturacio-clients') {
    taulaFacturacioClients();
  } else if (pageType[2] === 'nova-factura') {
    formFacturaClient(false);
  } else if (pageType[2] === 'modifica-factura') {
    formFacturaClient(true, id);
  } else if (pageType[2] === 'llistat-clients') {
    taulaLlistatClients();
  } else if (pageType[2] === 'nou-client') {
    formClient(false);
  } else if (pageType[2] === 'modifica-client') {
    formClient(true, id);
  }
}
