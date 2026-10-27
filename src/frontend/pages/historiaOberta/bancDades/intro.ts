import { getPageType } from '../../../utils/urlPath';
import { fitxaPersona } from '../../persona/fitxaPersona';
import { donesHomes } from './donesHomes';

const url = window.location.href;
const pageType = getPageType(url);

export function bancDadesWebPublica(): void {
  switch (pageType[2]) {
    case 'dones-homes': {
      const persona = pageType[3];

      if (persona) {
        fitxaPersona('persones/get/persona', persona);
      } else {
        donesHomes();
      }

      break;
    }

    // Añade aquí las futuras secciones de la base de datos.
    // case 'una-altra-seccio':
    //   ...
    //   break;

    default:
      break;
  }
}
