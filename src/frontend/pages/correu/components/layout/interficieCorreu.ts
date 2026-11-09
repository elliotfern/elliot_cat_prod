import { crearElement, mostrarEstat } from '../../utils/dom';

export interface ElementsGestorCorreu {
  contenidorCarpetes: HTMLElement;
  contenidorMissatges: HTMLElement;
  contenidorLectura: HTMLElement;
  titolCarpeta: HTMLElement;
  campCerca: HTMLInputElement;
  botoActualitzar: HTMLButtonElement;
  botoRedactar: HTMLButtonElement;
}

export function crearInterficie(contenidor: HTMLElement): ElementsGestorCorreu {
  contenidor.replaceChildren();

  const gestor = crearElement('div', 'correu-gestor');

  const capcalera = crearElement('div', 'd-flex justify-content-between align-items-center mb-3');

  const titol = crearElement('h2', 'h4 mb-0', 'Gestor de correu');

  const accions = crearElement('div', 'd-flex gap-2');

  const botoRedactar = crearElement('button', 'btn btn-outline-primary btn-sm', 'Redactar');

  botoRedactar.type = 'button';

  const botoActualitzar = crearElement('button', 'btn btn-outline-secondary btn-sm', 'Actualitzar');

  botoActualitzar.type = 'button';

  accions.append(botoRedactar, botoActualitzar);
  capcalera.append(titol, accions);

  const fila = crearElement('div', 'row g-3');

  const columnaCarpetes = crearElement('div', 'col-12 col-lg-3');
  const columnaMissatges = crearElement('div', 'col-12 col-lg-4');
  const columnaLectura = crearElement('div', 'col-12 col-lg-5');

  // Panell de carpetes
  const panellCarpetes = crearElement('div', 'card h-100');
  const cosCarpetes = crearElement('div', 'card-body p-2');

  const contenidorCarpetes = crearElement('div', 'correu-carpetes');

  cosCarpetes.appendChild(contenidorCarpetes);
  panellCarpetes.appendChild(cosCarpetes);
  columnaCarpetes.appendChild(panellCarpetes);

  // Panell de missatges
  const panellMissatges = crearElement('div', 'card h-100');
  const cosMissatges = crearElement('div', 'card-body');

  const capcaleraMissatges = crearElement('div', 'd-flex justify-content-between align-items-center mb-3');

  const titolCarpeta = crearElement('h3', 'h6 mb-0', 'Safata d’entrada');

  const campCerca = crearElement('input', 'form-control form-control-sm');

  campCerca.type = 'search';
  campCerca.placeholder = 'Cercar missatges...';
  campCerca.setAttribute('aria-label', 'Cercar missatges');

  capcaleraMissatges.appendChild(titolCarpeta);

  const cerca = crearElement('div', 'mb-3');
  cerca.appendChild(campCerca);

  const contenidorMissatges = crearElement('div', 'correu-llista-missatges');

  cosMissatges.append(capcaleraMissatges, cerca, contenidorMissatges);

  panellMissatges.appendChild(cosMissatges);
  columnaMissatges.appendChild(panellMissatges);

  // Panell de lectura
  const panellLectura = crearElement('div', 'card h-100');
  const cosLectura = crearElement('div', 'card-body');

  const contenidorLectura = crearElement('div', 'correu-lectura');

  mostrarEstat(contenidorLectura, 'Selecciona un missatge per llegir-lo.');

  cosLectura.appendChild(contenidorLectura);
  panellLectura.appendChild(cosLectura);
  columnaLectura.appendChild(panellLectura);

  fila.append(columnaCarpetes, columnaMissatges, columnaLectura);

  gestor.append(capcalera, fila);
  contenidor.appendChild(gestor);

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
