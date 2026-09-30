import { getPageType } from '../../utils/urlPath';
import { adreces } from '../adreces/adreces';
import { agenda } from '../agenda/agenda';
import { areaPrivadaUsuaris } from '../areaPrivadaUsuaris/funcions';
import { auxiliars } from '../auxiliars/auxiliars';
import { galeriaImatgesPublica } from '../auxiliars/fitxaGaleriaImatgesPublica';
import { obtenerTemperaturaTrento } from '../auxiliars/meteoTrento';
import { biblioteca } from '../biblioteca/biblioteca';
import { blog } from '../blog/blog';
import { cinema } from '../cinema/funcions';
import { comptabilitat } from '../comptabilitat/comptabilitat';
import { contactes } from '../contactes/contactes';
import { curriculum } from '../curriculum/curriculum';
import { usuaris } from '../gestioUsuaris/usuaris';
import { historiaOberta } from '../historiaOberta/historiaOberta';
import { lectorRss } from '../lectorRss/lectorRss';
import { persona } from '../persona/persona';
import { projectes } from '../projectes/projectes';
import { radio } from '../radio/radio';
import { salut } from '../salut/salut';
import { vault } from '../vault/funcions';
import { viatges } from '../viatges/viatges';

const url = window.location.href;
const pageType = getPageType(url);

export async function intranet_admin() {
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
    areaPrivadaUsuaris();
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
