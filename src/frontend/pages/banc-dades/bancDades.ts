import { fitxaPersona } from '../persona/fitxaPersona';
import { donesHomes } from './donesHomes';

export function bancDadesWebPublica(pageType: string[]): void {
  switch (pageType[1]) {
    case 'dones-homes': {
      const persona = pageType[2];

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
