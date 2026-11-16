import { crearElement, mostrarEstat } from '../../utils/dom';

export interface ElementsGestorCorreu {
  gestor: HTMLElement;
  contenidorCarpetes: HTMLElement;
  contenidorMissatges: HTMLElement;
  contenidorLectura: HTMLElement;
  titolCarpeta: HTMLElement;
  campCerca: HTMLInputElement;
  botoActualitzar: HTMLButtonElement;
  botoRedactar: HTMLButtonElement;
  botoCarpetes: HTMLButtonElement;
  botoTornarDesDeCarpetes: HTMLButtonElement;
  botoTornarMissatges: HTMLButtonElement;
  botoPaginaAnterior: HTMLButtonElement;
  botoPaginaSeguent: HTMLButtonElement;
  indicadorPaginacio: HTMLElement;
}

function instal·larEstils(): void {
  if (document.getElementById('correu-layout-styles')) {
    return;
  }

  const estils = document.createElement('style');
  estils.id = 'correu-layout-styles';

  estils.textContent = `
    .correu-gestor {
      display: flex;
      flex-direction: column;
    }

    .correu-mobil-capcalera {
      display: none;
    }

    /* Escriptori: mantenim les tres columnes simultànies. */
    @media (min-width: 992px) {
      .correu-gestor {
        height: var(--correu-alcada, auto);
      }

      .correu-gestor .correu-fila {
        flex: 1 1 0;
        min-height: 0;
      }

      .correu-gestor .correu-columna {
        height: 100%;
        min-height: 0;
      }

      .correu-gestor .correu-panell {
        display: flex;
        flex-direction: column;
        width: 100%;
        height: 100%;
        min-height: 0;
      }

      .correu-gestor .correu-cos {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        min-height: 0;
        overflow: hidden;
      }

      .correu-gestor .correu-scroll {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
      }

      .correu-gestor .correu-fix {
        flex-shrink: 0;
      }
    }

    /* Mòbil i tauleta: només es mostra una vista cada vegada. */
    @media (max-width: 991.98px) {
      .correu-gestor .correu-mobil-capcalera {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        border-bottom: 1px solid var(--bs-border-color);
      }

      .correu-gestor .correu-fila {
        display: block;
        margin: 0;
      }

      .correu-gestor .correu-columna {
        display: none !important;
        width: 100%;
        padding: 0;
      }

      .correu-gestor.vista-carpetes .correu-columna-carpetes,
      .correu-gestor.vista-missatges .correu-columna-missatges,
      .correu-gestor.vista-lectura .correu-columna-lectura {
        display: flex !important;
      }

      .correu-gestor .correu-panell {
        width: 100%;
        min-width: 0;
        border-radius: 0.5rem;
      }

      .correu-gestor .correu-cos {
        min-width: 0;
      }

      .correu-gestor .correu-scroll {
        min-width: 0;
        overflow-x: hidden;
      }

      .correu-gestor .correu-lectura {
        overflow-wrap: anywhere;
      }

      .correu-gestor .correu-lectura iframe {
        max-width: 100%;
      }

      .correu-gestor .correu-llista-missatges {
        min-width: 0;
      }

      .correu-gestor .correu-fix {
        flex-shrink: 0;
      }

      .correu-gestor .correu-capcalera-missatges {
        flex-wrap: wrap;
      }

      .correu-gestor .correu-navegacio-paginacio {
        gap: 0.5rem;
      }

      .correu-gestor .correu-navegacio-paginacio button {
        white-space: nowrap;
      }

      .correu-gestor .correu-navegacio-paginacio span {
        flex: 1;
      }
    }
  `;

  document.head.appendChild(estils);
}

export function crearInterficie(contenidor: HTMLElement): ElementsGestorCorreu {
  instal·larEstils();

  contenidor.replaceChildren();

  const gestor = crearElement('div', 'correu-gestor vista-missatges');

  // Capçalera general
  const capcalera = crearElement('div', 'd-flex justify-content-between align-items-center mb-3 correu-fix');

  const titol = crearElement('h2', 'h4 mb-0', 'Gestor de correu');

  const accions = crearElement('div', 'd-flex gap-2');

  const botoRedactar = crearElement('button', 'btn btn-outline-primary btn-sm', 'Redactar');
  botoRedactar.type = 'button';

  const botoActualitzar = crearElement('button', 'btn btn-outline-secondary btn-sm', 'Actualitzar');
  botoActualitzar.type = 'button';

  accions.append(botoRedactar, botoActualitzar);
  capcalera.append(titol, accions);

  const fila = crearElement('div', 'row g-3 correu-fila');

  // Columnes
  const columnaCarpetes = crearElement('div', 'col-12 col-lg-3 correu-columna correu-columna-carpetes d-flex');

  const columnaMissatges = crearElement('div', 'col-12 col-lg-4 correu-columna correu-columna-missatges d-flex');

  const columnaLectura = crearElement('div', 'col-12 col-lg-5 correu-columna correu-columna-lectura d-flex');

  // Panell de carpetes
  const panellCarpetes = crearElement('div', 'card correu-panell');

  const capcaleraCarpetesMobil = crearElement('div', 'correu-mobil-capcalera');

  const botoTornarDesDeCarpetes = crearElement('button', 'btn btn-outline-secondary btn-sm', '← Mensajes');
  botoTornarDesDeCarpetes.type = 'button';

  const titolCarpetesMobil = crearElement('strong', 'small', 'Carpetas');

  capcaleraCarpetesMobil.append(botoTornarDesDeCarpetes, titolCarpetesMobil);

  const cosCarpetes = crearElement('div', 'card-body p-2 correu-cos');

  const contenidorCarpetes = crearElement('div', 'correu-carpetes correu-scroll');

  cosCarpetes.appendChild(contenidorCarpetes);
  panellCarpetes.append(capcaleraCarpetesMobil, cosCarpetes);
  columnaCarpetes.appendChild(panellCarpetes);

  // Panell de missatges
  const panellMissatges = crearElement('div', 'card correu-panell');

  const cosMissatges = crearElement('div', 'card-body correu-cos');

  const capcaleraMissatges = crearElement('div', 'd-flex justify-content-between align-items-center mb-3 correu-fix correu-capcalera-missatges');

  const botoCarpetes = crearElement('button', 'btn btn-outline-secondary btn-sm', '☰ Carpetas');
  botoCarpetes.type = 'button';

  const titolCarpeta = crearElement('h3', 'h6 mb-0', 'Safata d’entrada');

  capcaleraMissatges.append(botoCarpetes, titolCarpeta);

  const cerca = crearElement('div', 'mb-3 correu-fix');

  const campCerca = crearElement('input', 'form-control form-control-sm');

  campCerca.type = 'search';
  campCerca.placeholder = 'Cercar missatges...';
  campCerca.setAttribute('aria-label', 'Cercar missatges');

  cerca.appendChild(campCerca);

  const contenidorMissatges = crearElement('div', 'correu-llista-missatges correu-scroll');

  // Navegació de paginació
  const navegacioPaginacio = crearElement('div', 'd-flex justify-content-between align-items-center mt-3 correu-fix correu-navegacio-paginacio');

  const botoPaginaAnterior = crearElement('button', 'btn btn-outline-secondary btn-sm', 'Anterior');

  botoPaginaAnterior.type = 'button';
  botoPaginaAnterior.disabled = true;

  const indicadorPaginacio = crearElement('span', 'small text-muted text-center');

  indicadorPaginacio.textContent = 'Mensajes 0–0';

  const botoPaginaSeguent = crearElement('button', 'btn btn-outline-secondary btn-sm', 'Siguiente');

  botoPaginaSeguent.type = 'button';
  botoPaginaSeguent.disabled = true;

  navegacioPaginacio.append(botoPaginaAnterior, indicadorPaginacio, botoPaginaSeguent);

  cosMissatges.append(capcaleraMissatges, cerca, contenidorMissatges, navegacioPaginacio);

  panellMissatges.appendChild(cosMissatges);
  columnaMissatges.appendChild(panellMissatges);

  // Panell de lectura
  const panellLectura = crearElement('div', 'card correu-panell');

  const capcaleraLecturaMobil = crearElement('div', 'correu-mobil-capcalera');

  const botoTornarMissatges = crearElement('button', 'btn btn-outline-secondary btn-sm', '← Mensajes');
  botoTornarMissatges.type = 'button';

  const titolLecturaMobil = crearElement('strong', 'small', 'Lectura del mensaje');

  capcaleraLecturaMobil.append(botoTornarMissatges, titolLecturaMobil);

  const cosLectura = crearElement('div', 'card-body correu-cos');

  const contenidorLectura = crearElement('div', 'correu-lectura correu-scroll');

  mostrarEstat(contenidorLectura, 'Selecciona un missatge per llegir-lo.');

  cosLectura.appendChild(contenidorLectura);

  panellLectura.append(capcaleraLecturaMobil, cosLectura);
  columnaLectura.appendChild(panellLectura);

  fila.append(columnaCarpetes, columnaMissatges, columnaLectura);

  gestor.append(capcalera, fila);
  contenidor.appendChild(gestor);

  // Adaptar l'alçada a l'espai real disponible en escriptori.
  const actualitzarAlcada = (): void => {
    if (window.innerWidth < 992) {
      gestor.style.removeProperty('--correu-alcada');
      return;
    }

    const posicioSuperior = gestor.getBoundingClientRect().top;
    const alçadaDisponible = window.innerHeight - posicioSuperior - 16;

    gestor.style.setProperty('--correu-alcada', `${Math.max(300, alçadaDisponible)}px`);
  };

  actualitzarAlcada();

  window.addEventListener('resize', actualitzarAlcada);

  const observador = new ResizeObserver(actualitzarAlcada);

  const capcaleraWeb = document.getElementById('siteHeader');
  const navegacioIntranet = document.getElementById('intranetNav');

  if (capcaleraWeb) {
    observador.observe(capcaleraWeb);
  }

  if (navegacioIntranet) {
    observador.observe(navegacioIntranet);
  }

  requestAnimationFrame(actualitzarAlcada);

  return {
    gestor,
    contenidorCarpetes,
    contenidorMissatges,
    contenidorLectura,
    titolCarpeta,
    campCerca,
    botoActualitzar,
    botoRedactar,
    botoCarpetes,
    botoTornarDesDeCarpetes,
    botoTornarMissatges,
    botoPaginaAnterior,
    botoPaginaSeguent,
    indicadorPaginacio,
  };
}
