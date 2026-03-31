import { getPageType } from '../../utils/urlPath';
import { detallsFacturaClients } from './detallsFacturaClient';
import { formClient } from './formClient';
import { formEmissor } from './formEmissor';
import { formFacturaClient } from './formFacturaClient';
import { formFacturaProducte } from './formFacturaProducte';
import { formProducte } from './formProducte';
import { taulaFacturacioClients } from './taulaFacturacioClients';
import { taulaDespeses } from './taulaFacturacioProveidors';
import { taulaLlistatClients } from './taulaLlistatClients';
import { taulaLlistatEmissors } from './taulaLlistatEmissors';
import { taulaLlistatProductes } from './taulaLlistatProductes';

export function comptabilitat() {
  const url = window.location.href;
  const pageType = getPageType(url);
  const id = parseInt(pageType[3], 10);

  if (pageType[2] === 'facturacio-clients-partita-iva') {
    const emissor = 3;
    taulaFacturacioClients(emissor);
  } else if (pageType[2] === 'facturacio-clients-autonom-irlanda') {
    const emissor = 2;
    taulaFacturacioClients(emissor);
  } else if (pageType[2] === 'facturacio-clients-hispantic') {
    const emissor = 1;
    taulaFacturacioClients(emissor);
  } else if (pageType[2] === 'nova-factura') {
    formFacturaClient(false);
  } else if (pageType[2] === 'modifica-factura') {
    formFacturaClient(true, id);
  } else if (pageType[2] === 'fitxa-factura-client') {
    detallsFacturaClients();
  } else if (pageType[2] === 'nou-producte-factura') {
    formFacturaProducte(false);
  } else if (pageType[2] === 'modifica-producte-factura') {
    formFacturaProducte(true, id);
  } else if (pageType[2] === 'llistat-clients') {
    taulaLlistatClients();
  } else if (pageType[2] === 'nou-client') {
    formClient(false);
  } else if (pageType[2] === 'modifica-client') {
    formClient(true, id);
  } else if (pageType[2] === 'llistat-emissors') {
    taulaLlistatEmissors();
  } else if (pageType[2] === 'nou-emissor') {
    formEmissor(false);
  } else if (pageType[2] === 'modifica-emissor') {
    formEmissor(true, id);
  } else if (pageType[2] === 'llistat-productes') {
    taulaLlistatProductes();
  } else if (pageType[2] === 'nou-producte') {
    formProducte(false);
  } else if (pageType[2] === 'modifica-producte') {
    formProducte(true, id);
  } else if (pageType[2] === 'facturacio-proveidors-partita-iva') {
    const emissor = 2;
    taulaDespeses(emissor);
  } else if (pageType[2] === 'facturacio-proveidors-autonom-irlanda') {
    const emissor = 2;
    taulaDespeses(emissor);
  } else if (pageType[2] === 'facturacio-proveidors-hispantic') {
    const emissor = 1;
    taulaDespeses(emissor);
  }
}
