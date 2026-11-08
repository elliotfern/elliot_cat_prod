import { CarpetaCorreu } from '../../types/Correu';
import { crearElement } from '../../utils/dom';

const carpetesObertes = new Set<string>();

export function renderitzarCarpetes(contenidor: HTMLElement, carpetes: CarpetaCorreu[], carregar: (carpeta: CarpetaCorreu) => void, carpetaActual: string): void {
  contenidor.replaceChildren();

  contenidor.classList.remove('list-group-flush');
  contenidor.classList.add('d-flex', 'flex-column', 'gap-2', 'p-2');

  const fills = new Map<string, CarpetaCorreu[]>();

  for (const carpeta of carpetes) {
    const pare = carpeta.ruta_pare;

    if (pare === null || pare === 'INBOX') {
      continue;
    }

    const germans = fills.get(pare) ?? [];
    germans.push(carpeta);
    fills.set(pare, germans);
  }

  const carpetesArrel = carpetes.filter((carpeta) => carpeta.tipus !== 'other' || carpeta.ruta_pare === null || carpeta.ruta_pare === 'INBOX');

  const actualitzarCarpetaActiva = (ruta: string): void => {
    contenidor.querySelectorAll<HTMLButtonElement>('[data-carpeta-ruta]').forEach((boto) => {
      const activa = boto.dataset.carpetaRuta === ruta;

      boto.classList.toggle('active', activa);
      boto.setAttribute('aria-pressed', String(activa));
    });
  };

  const renderitzar = (carpeta: CarpetaCorreu, nivell: number): void => {
    const subcarpetes = fills.get(carpeta.ruta) ?? [];
    const teFills = subcarpetes.length > 0;
    const oberta = carpetesObertes.has(carpeta.ruta);

    const fila = crearElement('div', 'd-flex align-items-center gap-1');

    const boto = crearElement('button', 'btn btn-outline-secondary btn-sm text-start d-flex justify-content-between align-items-center flex-grow-1');

    boto.type = 'button';
    boto.dataset.carpetaRuta = carpeta.ruta;
    boto.style.paddingLeft = `${12 + nivell * 18}px`;
    boto.style.minWidth = '0';

    const nom = crearElement('span', `text-break ${nivell === 0 ? 'fw-semibold' : ''}`, carpeta.nom_visual);

    const esquerra = crearElement('span', 'd-flex align-items-center flex-grow-1');

    esquerra.style.minWidth = '0';
    esquerra.appendChild(nom);

    boto.appendChild(esquerra);

    if (carpeta.missatges_no_llegits > 0) {
      boto.appendChild(crearElement('span', 'badge text-bg-primary rounded-pill ms-2', String(carpeta.missatges_no_llegits)));
    }

    const activa = carpeta.ruta === carpetaActual;

    boto.classList.toggle('active', activa);
    boto.setAttribute('aria-pressed', String(activa));

    boto.addEventListener('click', () => {
      actualitzarCarpetaActiva(carpeta.ruta);
      carregar(carpeta);
    });

    fila.appendChild(boto);

    if (teFills) {
      const botoDesplegar = crearElement('button', 'btn btn-outline-secondary btn-sm flex-shrink-0');

      botoDesplegar.type = 'button';

      botoDesplegar.setAttribute('aria-label', oberta ? 'Plegar carpeta' : 'Desplegar carpeta');

      botoDesplegar.appendChild(crearElement('span', 'small', oberta ? '▼' : '▶'));

      botoDesplegar.addEventListener('click', () => {
        if (oberta) {
          carpetesObertes.delete(carpeta.ruta);
        } else {
          carpetesObertes.add(carpeta.ruta);
        }

        renderitzarCarpetes(contenidor, carpetes, carregar, carpetaActual);
      });

      fila.appendChild(botoDesplegar);
    }

    contenidor.appendChild(fila);

    if (oberta) {
      for (const subcarpeta of subcarpetes) {
        renderitzar(subcarpeta, nivell + 1);
      }
    }
  };

  for (const carpeta of carpetesArrel) {
    renderitzar(carpeta, 0);
  }
}
