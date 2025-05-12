import 'trix/dist/trix.css';
import 'trix';
import './estils/style.css';

import { getPageType } from './utils/urlPath';
import { cinema } from './pages/cinema/funcions';
import { loginPage } from './pages/login/funcions';
import { vault } from './pages/vault/funcions';
import { historiaOberta } from './pages/historiaOberta/historiaOberta';
import { biblioteca } from './pages/biblioteca/biblioteca';
import { adreces } from './pages/adreces/adreces';
import { persona } from './pages/persona/persona';
import { viatges } from './pages/viatges/viatges';
import { comptabilitat } from './pages/comptabilitat/comptabilitat';
import { barraNavegacio } from './components/barraNavegacio/barraNavegacio';
import { mostrarBotonsNomesAdmin } from './components/mostrarBotons/mostrarBoton';
import { auxiliars } from './pages/auxiliars/auxiliars';
import { logout } from './services/login/logOutApi';
import { contactes } from './pages/contactes/contactes';
import { lectorRss } from './pages/lectorRss/lectorRss';

const url = window.location.href;
const pageType = getPageType(url);

document.addEventListener('DOMContentLoaded', () => {
  barraNavegacio();
  mostrarBotonsNomesAdmin();

  const logoutButton = document.getElementById('logoutButton');
  if (logoutButton) {
    logoutButton.addEventListener('click', logout);
  }

  console.log(pageType);
  if (pageType[1] === 'entrada') {
    loginPage();
  } else if (pageType[1] === 'claus-privades') {
    vault();
  } else if (pageType[1] === 'comptabilitat') {
    comptabilitat();
  } else if (pageType[1] === 'auxiliars') {
    auxiliars();
  } else if (pageType[1] === 'agenda-contactes') {
    contactes();

    // Part accessible tant a usuaris com a visitants
  } else if (pageType[1] === 'lector-rss' || pageType[0] === 'lector-rss') {
    lectorRss();
  } else if (pageType[1] === 'historia' || pageType[0] === 'historia') {
    historiaOberta();
  } else if (pageType[1] === 'biblioteca' || pageType[0] === 'biblioteca') {
    biblioteca();
  } else if (pageType[1] === 'adreces' || pageType[0] === 'adreces') {
    adreces();
  } else if (pageType[1] === 'base-dades-persones' || pageType[0] === 'base-dades-persones') {
    persona();
  } else if (pageType[1] === 'viatges' || pageType[0] === 'viatges') {
    viatges();
  } else if (pageType[1] === 'cinema' || pageType[0] === 'cinema') {
    cinema();
  }
});
