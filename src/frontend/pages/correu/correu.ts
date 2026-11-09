import DOMPurify from 'dompurify';

import { obtenirCarpetes, obtenirMissatges, obtenirMissatge, marcarMissatgeLlegit, enviarMissatge } from './services/correuApi';

import { renderitzarCarpetes } from './components/carpetes/carpetes';
import { renderitzarMissatges } from './components/missatges/llistaMissatges';
import { renderitzarLectura } from './components/missatges/lecturaMissatge';
import { crearFormulariRedaccio } from './components/redaccio/formulariRedaccio';

import type { OpcionsRedaccio } from './components/redaccio/formulariRedaccio';

import type { CarpetaCorreu, ContacteCorreu, MissatgeDetall, MissatgeLlista } from './types/Correu';

import { crearElement, mostrarEstat } from './utils/dom';
import { formatarData } from './utils/formatData';

interface ElementsGestorCorreu {
  contenidorCarpetes: HTMLElement;
  contenidorMissatges: HTMLElement;
  contenidorLectura: HTMLElement;
  titolCarpeta: HTMLElement;
  campCerca: HTMLInputElement;
  botoActualitzar: HTMLButtonElement;
  botoRedactar: HTMLButtonElement;
}

const ADRECA_PROPIA = 'elliot@hispantic.com';

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

/**
 * Escapa texto para incorporarlo de forma segura a HTML.
 */
function escaparHtml(text: string): string {
  return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

/**
 * Normaliza una dirección de correo para comparaciones.
 */
function normalitzarEmail(email: string): string {
  return email.trim().toLowerCase();
}

/**
 * Obtiene el contenido HTML original, sanitizado.
 */
function obtenirHtmlOriginal(missatge: MissatgeDetall): string {
  if (missatge.contingut_html) {
    return DOMPurify.sanitize(missatge.contingut_html, {
      USE_PROFILES: { html: true },
    });
  }

  return `<pre>${escaparHtml(missatge.contingut_text ?? '')}</pre>`;
}

/**
 * Obtiene el contenido textual original.
 */
function obtenirTextOriginal(missatge: MissatgeDetall): string {
  if (missatge.contingut_text?.trim()) {
    return missatge.contingut_text;
  }

  const contenidor = document.createElement('div');
  contenidor.innerHTML = obtenirHtmlOriginal(missatge);

  return contenidor.innerText || contenidor.textContent || '';
}

/**
 * Construye el contenido citado para una respuesta o un reenvío.
 *
 * El contenido se mantiene separado del texto nuevo para que
 * el backend pueda insertar la firma entre ambos.
 */
function obtenirContingutCitat(
  missatge: MissatgeDetall,
  reenviament = false
): {
  text: string;
  html: string;
} {
  const htmlOriginal = obtenirHtmlOriginal(missatge);
  const textOriginal = obtenirTextOriginal(missatge);

  const remitent = missatge.remitent.nom ? `${missatge.remitent.nom} <${missatge.remitent.email}>` : missatge.remitent.email;

  if (!reenviament) {
    const introduccio = `El ${formatarData(missatge.data)}, ${remitent} escribió:`;

    const textCitat = textOriginal
      .split(/\r?\n/)
      .map((linia) => `> ${linia}`)
      .join('\n');

    return {
      text: `${introduccio}\n${textCitat}`,
      html: `
        <blockquote type="cite">
          <p>${escaparHtml(introduccio)}</p>
          ${htmlOriginal}
        </blockquote>
      `,
    };
  }

  const destinataris = missatge.destinataris.map((destinatari) => destinatari.email).join(', ');

  const copia = missatge.copia.map((destinatari) => destinatari.email).join(', ');

  const capcalera = ['---------- Mensaje reenviado ----------', `De: ${remitent}`, `Fecha: ${formatarData(missatge.data)}`, ...(destinataris ? [`Para: ${destinataris}`] : []), ...(copia ? [`CC: ${copia}`] : []), `Asunto: ${missatge.assumpte}`];

  const capcaleraHtml = capcalera.map((linia) => `<p>${escaparHtml(linia)}</p>`).join('');

  return {
    text: `${capcalera.join('\n')}\n\n${textOriginal}`,
    html: `
      <div class="correu-reenviament">
        <hr>
        ${capcaleraHtml}
        ${htmlOriginal}
      </div>
    `,
  };
}

/**
 * Recupera las referencias anteriores de una conversación.
 *
 * El backend añadirá después el Message-ID del mensaje original
 * al construir la cabecera References.
 */
function obtenirReferenciesResposta(missatge: MissatgeDetall): string[] {
  const referencies = missatge.referencies.length > 0 ? missatge.referencies : missatge.in_reply_to ? [missatge.in_reply_to] : [];

  return [...new Set(referencies)];
}

/**
 * Añade Re: o Fwd: sin duplicar el prefijo.
 */
function afegirPrefixAssumpte(assumpte: string, prefix: 'Re' | 'Fwd'): string {
  const assumpteNet = assumpte.trim();

  const prefixRegex = prefix === 'Re' ? /^re\s*:/i : /^fwd\s*:/i;

  return prefixRegex.test(assumpteNet) ? assumpteNet : `${prefix}: ${assumpteNet}`;
}

/**
 * Añade un destinatario si no es la dirección propia
 * y todavía no está presente en la lista.
 */
function afegirDestinatariUnic(resultat: ContacteCorreu[], vistos: Set<string>, destinatari: ContacteCorreu): void {
  const email = destinatari.email.trim();
  const emailNormalitzat = normalitzarEmail(email);

  if (!email || emailNormalitzat === normalitzarEmail(ADRECA_PROPIA) || vistos.has(emailNormalitzat)) {
    return;
  }

  vistos.add(emailNormalitzat);

  resultat.push({
    nom: destinatari.nom,
    email,
  });
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

/**
 * Abre el formulario de redacción con una configuración inicial.
 */
function obrirRedaccio(opcions: OpcionsRedaccio = {}): void {
  if (!elements) {
    return;
  }

  redaccioActiva = true;
  actualitzarBotoRedaccio();

  elements.contenidorLectura.replaceChildren(crearFormulariRedaccio(enviarMissatge, opcions));
}

/**
 * Inicia una respuesta al remitente original.
 */
function iniciarResposta(missatge: MissatgeDetall): void {
  const destinataris: ContacteCorreu[] = [];
  const vistos = new Set<string>();

  const remitentEsPropi = normalitzarEmail(missatge.remitent.email) === normalitzarEmail(ADRECA_PROPIA);

  if (remitentEsPropi) {
    for (const destinatari of missatge.destinataris) {
      afegirDestinatariUnic(destinataris, vistos, destinatari);
    }
  } else {
    afegirDestinatariUnic(destinataris, vistos, missatge.remitent);
  }

  const citat = obtenirContingutCitat(missatge);

  obrirRedaccio({
    destinataris,
    assumpte: afegirPrefixAssumpte(missatge.assumpte, 'Re'),
    inReplyTo: missatge.message_id,
    referencies: obtenirReferenciesResposta(missatge),
    contingutCitatText: citat.text,
    contingutCitatHtml: citat.html,
  });
}

/**
 * Inicia una respuesta a todos los destinatarios originales.
 */
function iniciarRespostaTots(missatge: MissatgeDetall): void {
  const destinataris: ContacteCorreu[] = [];
  const copia: ContacteCorreu[] = [];

  const vistos = new Set<string>();

  const remitentEsPropi = normalitzarEmail(missatge.remitent.email) === normalitzarEmail(ADRECA_PROPIA);

  if (!remitentEsPropi) {
    afegirDestinatariUnic(destinataris, vistos, missatge.remitent);
  }

  for (const destinatari of missatge.destinataris) {
    afegirDestinatariUnic(destinataris, vistos, destinatari);
  }

  for (const destinatari of missatge.copia) {
    afegirDestinatariUnic(copia, vistos, destinatari);
  }

  const citat = obtenirContingutCitat(missatge);

  obrirRedaccio({
    destinataris,
    copia,
    assumpte: afegirPrefixAssumpte(missatge.assumpte, 'Re'),
    inReplyTo: missatge.message_id,
    referencies: obtenirReferenciesResposta(missatge),
    contingutCitatText: citat.text,
    contingutCitatHtml: citat.html,
  });
}

/**
 * Inicia un reenvío del mensaje original.
 */
function iniciarReenviament(missatge: MissatgeDetall): void {
  const citat = obtenirContingutCitat(missatge, true);

  obrirRedaccio({
    assumpte: afegirPrefixAssumpte(missatge.assumpte, 'Fwd'),
    contingutCitatText: citat.text,
    contingutCitatHtml: citat.html,
  });
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

    renderitzarLectura(elements.contenidorLectura, detall, {
      respondre: iniciarResposta,
      respondreTots: iniciarRespostaTots,
      reenviar: iniciarReenviament,
    });

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
