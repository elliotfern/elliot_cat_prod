import { obtenirCarpetes, obtenirMissatges, obtenirMissatge, marcarMissatgeLlegit, enviarMissatge } from './services/correuApi';

import { renderitzarCarpetes } from './components/carpetes/carpetes';
import { renderitzarMissatges } from './components/missatges/llistaMissatges';
import { renderitzarLectura } from './components/missatges/lecturaMissatge';
import { crearFormulariRedaccio } from './components/redaccio/formulariRedaccio';

import type { CarpetaCorreu, MissatgeDetall, MissatgeLlista } from './types/Correu';

import { crearElement, mostrarEstat } from './utils/dom';

interface ElementsGestorCorreu {
  contenidorCarpetes: HTMLElement;
  contenidorMissatges: HTMLElement;
  contenidorLectura: HTMLElement;
  titolCarpeta: HTMLElement;
  campCerca: HTMLInputElement;
  botoActualitzar: HTMLButtonElement;
  botoRedactar: HTMLButtonElement;
}

let carpetaActual = 'INBOX';
let missatgesActuals: MissatgeLlista[] = [];
let missatgeSeleccionat: number | null = null;
let redaccioActiva = false;

let elements: ElementsGestorCorreu | null = null;

function actualitzarBotoRedaccio(): void {
  if (!elements) {
    return;
  }

  elements.botoRedactar.textContent = redaccioActiva ? 'Tancar redacció' : 'Redactar';

  elements.botoRedactar.classList.toggle('btn-outline-secondary', redaccioActiva);

  elements.botoRedactar.classList.toggle('btn-outline-primary', !redaccioActiva);
}

function crearInterficie(contenidor: HTMLElement): ElementsGestorCorreu {
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

  const panellCarpetes = crearElement('div', 'card h-100');
  const cosCarpetes = crearElement('div', 'card-body p-2');

  const contenidorCarpetes = crearElement('div', 'correu-carpetes');

  cosCarpetes.appendChild(contenidorCarpetes);
  panellCarpetes.appendChild(cosCarpetes);
  columnaCarpetes.appendChild(panellCarpetes);

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

function actualitzarLlistaMissatges(): void {
  if (!elements) {
    return;
  }

  renderitzarMissatges(elements.contenidorMissatges, missatgesActuals, obrirMissatge, elements.campCerca.value, missatgeSeleccionat);
}

function obrirRedaccio(): void {
  if (!elements) {
    return;
  }

  redaccioActiva = true;
  actualitzarBotoRedaccio();

  elements.contenidorLectura.replaceChildren(crearFormulariRedaccio(enviarMissatge));
}

function tancarRedaccio(): void {
  if (!elements) {
    return;
  }

  redaccioActiva = false;
  actualitzarBotoRedaccio();

  const missatgeAnterior = missatgesActuals.find((missatge) => missatge.id === missatgeSeleccionat);

  if (missatgeAnterior) {
    void obrirMissatge(missatgeAnterior);
    return;
  }

  mostrarEstat(elements.contenidorLectura, 'Selecciona un missatge per llegir-lo.');
}

async function carregarMissatges(carpeta: string): Promise<void> {
  if (!elements) {
    return;
  }

  redaccioActiva = false;
  actualitzarBotoRedaccio();

  carpetaActual = carpeta;
  missatgesActuals = [];
  missatgeSeleccionat = null;

  elements.titolCarpeta.textContent = carpeta;

  mostrarEstat(elements.contenidorMissatges, 'Carregant missatges...');

  mostrarEstat(elements.contenidorLectura, 'Selecciona un missatge per llegir-lo.');

  try {
    missatgesActuals = await obtenirMissatges(carpeta);

    actualitzarLlistaMissatges();
  } catch (error: unknown) {
    const missatge = error instanceof Error ? error.message : 'No s’han pogut carregar els missatges.';

    mostrarEstat(elements.contenidorMissatges, missatge);
  }
}

async function obrirMissatge(missatge: MissatgeLlista): Promise<void> {
  if (!elements) {
    return;
  }

  redaccioActiva = false;
  actualitzarBotoRedaccio();

  missatgeSeleccionat = missatge.id;

  mostrarEstat(elements.contenidorLectura, 'Carregant missatge...');

  try {
    const detall: MissatgeDetall = await obtenirMissatge(carpetaActual, missatge);

    renderitzarLectura(elements.contenidorLectura, detall);

    if (!missatge.llegit) {
      await marcarMissatgeLlegit(carpetaActual, missatge);

      missatge.llegit = true;
      actualitzarLlistaMissatges();
    }
  } catch (error: unknown) {
    const missatgeError = error instanceof Error ? error.message : 'No s’ha pogut obrir el missatge.';

    mostrarEstat(elements.contenidorLectura, missatgeError);
  }
}

async function carregarCarpetes(): Promise<void> {
  if (!elements) {
    return;
  }

  mostrarEstat(elements.contenidorCarpetes, 'Carregant carpetes...');

  try {
    const carpetes: CarpetaCorreu[] = await obtenirCarpetes();

    renderitzarCarpetes(
      elements.contenidorCarpetes,
      carpetes,
      (carpeta) => {
        void carregarMissatges(carpeta.ruta);
      },
      carpetaActual
    );
  } catch (error: unknown) {
    const missatge = error instanceof Error ? error.message : 'No s’han pogut carregar les carpetes.';

    mostrarEstat(elements.contenidorCarpetes, missatge);
  }
}

async function actualitzarGestorCorreu(): Promise<void> {
  if (!elements) {
    return;
  }

  elements.botoActualitzar.disabled = true;

  try {
    await carregarCarpetes();
    await carregarMissatges(carpetaActual);
  } finally {
    if (elements) {
      elements.botoActualitzar.disabled = false;
    }
  }
}

export async function inicialitzarGestorCorreu(): Promise<void> {
  const contenidor = document.getElementById('gestorCorreu');

  if (!contenidor) {
    console.error('No s’ha trobat el contenidor #gestorCorreu.');
    return;
  }

  elements = crearInterficie(contenidor);

  elements.campCerca.addEventListener('input', () => {
    actualitzarLlistaMissatges();
  });

  elements.botoActualitzar.addEventListener('click', () => {
    void actualitzarGestorCorreu();
  });

  elements.botoRedactar.addEventListener('click', () => {
    if (redaccioActiva) {
      tancarRedaccio();
    } else {
      obrirRedaccio();
    }
  });

  await carregarCarpetes();
  await carregarMissatges(carpetaActual);
}
