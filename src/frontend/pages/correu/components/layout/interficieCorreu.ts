import { crearElement } from '../../utils/dom';

export interface ElementsGestorCorreu {
  contenidorCarpetes: HTMLElement;
  contenidorMissatges: HTMLElement;
  contenidorLectura: HTMLElement;
  titolCarpeta: HTMLElement;
  campCerca: HTMLInputElement;
  botoActualitzar: HTMLButtonElement;
  botoRedactar: HTMLButtonElement;
}

/**
 * Ajusta la zona de trabajo a l'altura disponible de la finestra.
 * En escriptori, cada panell disposa del seu propi scroll.
 * En mòbil, recuperem el desplaçament natural de la pàgina.
 */
function configurarScrollPanells(fila: HTMLElement, contenidors: HTMLElement[]): void {
  function ajustarAlcada(): void {
    const escriptori = window.matchMedia('(min-width: 992px)').matches;

    if (!escriptori) {
      fila.style.height = 'auto';

      contenidors.forEach((contenidor) => {
        contenidor.style.flex = 'initial';
        contenidor.style.overflowY = 'visible';
        contenidor.style.minHeight = 'auto';
      });

      return;
    }

    const posicioDocument = fila.getBoundingClientRect().top + window.scrollY;

    const alcadaDisponible = Math.max(280, window.innerHeight - posicioDocument - 16);

    fila.style.height = `${alcadaDisponible}px`;

    contenidors.forEach((contenidor) => {
      contenidor.style.flex = '1 1 auto';
      contenidor.style.overflowY = 'auto';
      contenidor.style.minHeight = '0';
    });
  }

  requestAnimationFrame(ajustarAlcada);

  window.addEventListener('resize', ajustarAlcada);
}

export function crearInterficie(contenidor: HTMLElement): ElementsGestorCorreu {
  contenidor.replaceChildren();

  const gestor = crearElement('div', 'correu-gestor');
  gestor.classList.add('d-flex', 'flex-column');

  const capcalera = crearElement('div', 'd-flex justify-content-between align-items-center mb-3');

  const titol = crearElement('h2', 'h4 mb-0', 'Gestor de correu');

  const accions = crearElement('div', 'd-flex gap-2');

  const botoRedactar = crearElement('button', 'btn btn-outline-primary btn-sm', 'Redactar');

  botoRedactar.type = 'button';

  const botoActualitzar = crearElement('button', 'btn btn-outline-secondary btn-sm', 'Actualitzar');

  botoActualitzar.type = 'button';

  accions.append(botoRedactar, botoActualitzar);
  capcalera.append(titol, accions);

  const fila = crearElement('div', 'row g-3 flex-grow-1');
  fila.style.minHeight = '0';

  const columnaCarpetes = crearElement('div', 'col-12 col-lg-3');

  const columnaMissatges = crearElement('div', 'col-12 col-lg-4');

  const columnaLectura = crearElement('div', 'col-12 col-lg-5');

  // Panell de carpetes
  const panellCarpetes = crearElement('div', 'card h-100 d-flex flex-column overflow-hidden');

  const cosCarpetes = crearElement('div', 'card-body p-2 d-flex flex-column flex-grow-1');

  cosCarpetes.style.minHeight = '0';

  const contenidorCarpetes = crearElement('div', 'correu-carpetes');

  cosCarpetes.appendChild(contenidorCarpetes);
  panellCarpetes.appendChild(cosCarpetes);
  columnaCarpetes.appendChild(panellCarpetes);

  // Panell de missatges
  const panellMissatges = crearElement('div', 'card h-100 d-flex flex-column overflow-hidden');

  const cosMissatges = crearElement('div', 'card-body d-flex flex-column flex-grow-1');

  cosMissatges.style.minHeight = '0';

  const capcaleraMissatges = crearElement('div', 'd-flex justify-content-between align-items-center mb-3 flex-shrink-0');

  const titolCarpeta = crearElement('h3', 'h6 mb-0', 'Safata d’entrada');

  const campCerca = crearElement('input', 'form-control form-control-sm');

  campCerca.type = 'search';
  campCerca.placeholder = 'Cercar missatges...';

  campCerca.setAttribute('aria-label', 'Cercar missatges');

  capcaleraMissatges.appendChild(titolCarpeta);

  const cerca = crearElement('div', 'mb-3 flex-shrink-0');

  cerca.appendChild(campCerca);

  const contenidorMissatges = crearElement('div', 'correu-llista-missatges flex-grow-1');

  cosMissatges.append(capcaleraMissatges, cerca, contenidorMissatges);

  panellMissatges.appendChild(cosMissatges);
  columnaMissatges.appendChild(panellMissatges);

  // Panell de lectura
  const panellLectura = crearElement('div', 'card h-100 d-flex flex-column overflow-hidden');

  const cosLectura = crearElement('div', 'card-body d-flex flex-column flex-grow-1');

  cosLectura.style.minHeight = '0';

  const contenidorLectura = crearElement('div', 'correu-lectura flex-grow-1');

  cosLectura.appendChild(contenidorLectura);
  panellLectura.appendChild(cosLectura);
  columnaLectura.appendChild(panellLectura);

  fila.append(columnaCarpetes, columnaMissatges, columnaLectura);

  gestor.append(capcalera, fila);
  contenidor.appendChild(gestor);

  configurarScrollPanells(fila, [contenidorCarpetes, contenidorMissatges, contenidorLectura]);

  return {
    contenidorCarpetes,
    contenidorMissatges,
    contenidorLectura,
    titolCarpeta,
    campCerca,
    botoActualitzar,
    botoRedactar,
  };
}
