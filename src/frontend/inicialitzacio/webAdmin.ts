import { adreces } from '../pages/adreces/adreces';
import { agenda } from '../pages/agenda/agenda';
import { auxiliars } from '../pages/auxiliars/auxiliars';
import { galeriaImatgesPublica } from '../pages/auxiliars/fitxaGaleriaImatgesPublica';
import { obtenerTemperaturaTrento } from '../pages/auxiliars/meteoTrento';
import { biblioteca } from '../pages/biblioteca/biblioteca';
import { blog } from '../pages/blog/blog';
import { cinema } from '../pages/cinema/funcions';
import { comptabilitat } from '../pages/comptabilitat/comptabilitat';
import { contactes } from '../pages/contactes/contactes';
import { curriculum } from '../pages/curriculum/curriculum';
import { usuaris } from '../pages/gestioUsuaris/usuaris';
import { historiaOberta } from '../pages/historiaOberta/historiaOberta';
import { lectorRss } from '../pages/lectorRss/lectorRss';
import { persona } from '../pages/persona/persona';
import { projectes } from '../pages/projectes/projectes';
import { radio } from '../pages/radio/radio';
import { salut } from '../pages/salut/salut';
import { vault } from '../pages/vault/funcions';
import { viatges } from '../pages/viatges/viatges';

export async function webAdmin(pageType: string[]) {
  if (pageType[1] === 'claus-privades') {
    vault();
  } else if (pageType[1] === 'comptabilitat') {
    comptabilitat();
  } else if (pageType[1] === 'auxiliars') {
    auxiliars();
  } else if (pageType[1] === 'agenda-contactes') {
    contactes();
  } else if (pageType[1] === 'curriculum') {
    curriculum();
  } else if (pageType[1] === 'agenda') {
    agenda();
  } else if (pageType[1] === 'salut') {
    salut();
  } else if (pageType[1] === 'radio') {
    radio();
  } else if (pageType.includes('projectes')) {
    projectes();
    // Part accessible tant a usuaris com a visitants
  } else if (pageType[1] === 'lector-rss') {
    lectorRss();
  } else if (pageType[1] === 'historia') {
    historiaOberta();
  } else if (pageType[1] === 'biblioteca') {
    biblioteca();
  } else if (pageType[1] === 'adreces') {
    adreces();
  } else if (pageType[1] === 'base-dades-persones') {
    persona();
  } else if (pageType[1] === 'viatges') {
    viatges();
  } else if (pageType[1] === 'cinema') {
    cinema();
  } else if (pageType[1] === 'gestio-usuaris') {
    usuaris();
  } else if (pageType[1] === 'usuaris') {
    usuaris();
  } else if (pageType[1] === 'blog') {
    blog();
  } else if (pageType[1] === 'imatges') {
    const id = pageType[2];
    galeriaImatgesPublica(id);
  } else if (pageType[0] === 'gestio' || pageType[1] === 'admin') {
    const temperaturaTrento = document.getElementById('temperaturaTrento');

    if (temperaturaTrento) {
      temperaturaTrento.innerHTML = await obtenerTemperaturaTrento();
    }
  }
}
