import { apiSymfony } from '../../Infrastructure/Api/Client/ApiClient';
import DOMPurify from 'dompurify';

interface CarpetaCorreu {
  nom: string;
  ruta: string;
  ruta_pare: string | null;
  nom_visual: string;
  tipus: 'inbox' | 'archive' | 'junk' | 'trash' | 'sent' | 'drafts' | 'other';
  total_missatges: number;
  missatges_no_llegits: number;
}

interface RespostaCarpetes {
  carpetes: CarpetaCorreu[];
}

interface ContacteCorreu {
  nom: string;
  email: string;
}

interface MissatgeLlista {
  id: number;
  uid_validity: number;
  carpeta: string;
  remitent: ContacteCorreu;
  destinataris: ContacteCorreu[];
  assumpte: string;
  data: string;
  llegit: boolean;
  destacat: boolean;
  te_adjunts: boolean;
}

interface MissatgeDetall {
  id: {
    uid: number;
    uid_validity: number;
  };
  carpeta: string;
  remitent: ContacteCorreu;
  destinataris: ContacteCorreu[];
  copia: ContacteCorreu[];
  copia_oculta: ContacteCorreu[];
  assumpte: string;
  contingut_text: string | null;
  contingut_html: string | null;
  data: string;
  llegit: boolean;
  destacat: boolean;
  te_adjunts: boolean;
  message_id: string | null;
  in_reply_to: string | null;
  referencies: string[];
}

const ENDPOINT_CARPETES = 'correu/carpetes';
const ENDPOINT_MISSATGES = 'correu/missatges';
const ENDPOINT_MISSATGE = 'correu/missatge';
const ENDPOINT_LLEGIT = 'correu/missatge/llegit';

const ICONES_CARPETES: Record<CarpetaCorreu['tipus'], string> = {
  inbox: 'bi-inbox',
  archive: 'bi-archive',
  junk: 'bi-shield-exclamation',
  trash: 'bi-trash',
  sent: 'bi-send',
  drafts: 'bi-file-earmark',
  other: 'bi-folder',
};

let carpetaActual = 'INBOX';
//let missatgeActual: MissatgeDetall | null = null;
let missatgesActuals: MissatgeLlista[] = [];

function crearElement<K extends keyof HTMLElementTagNameMap>(etiqueta: K, classes = '', text?: string): HTMLElementTagNameMap[K] {
  const element = document.createElement(etiqueta);
  element.className = classes;

  if (text !== undefined) {
    element.textContent = text;
  }

  return element;
}

function formatarData(data: string): string {
  const valor = new Date(data);

  if (Number.isNaN(valor.getTime())) {
    return data;
  }

  return new Intl.DateTimeFormat('es-ES', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  }).format(valor);
}

function mostrarEstat(contenidor: HTMLElement, missatge: string): void {
  contenidor.replaceChildren(crearElement('div', 'text-center text-body-secondary py-5', missatge));
}

function crearInterficie(contenidor: HTMLElement): {
  carpetes: HTMLElement;
  llista: HTMLElement;
  lectura: HTMLElement;
  titolCarpeta: HTMLElement;
  cerca: HTMLInputElement;
  botoActualitzar: HTMLButtonElement;
} {
  contenidor.replaceChildren();

  const principal = crearElement('div', 'correu-gestor border rounded overflow-hidden');
  const capcalera = crearElement('div', 'd-flex justify-content-between align-items-center border-bottom px-3 py-3');

  const titol = crearElement('h4', 'mb-0', 'Gestor de correu');
  const botoActualitzar = crearElement('button', 'btn btn-outline-secondary btn-sm');
  botoActualitzar.type = 'button';
  botoActualitzar.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Actualitzar';

  capcalera.append(titol, botoActualitzar);

  const contingut = crearElement('div', 'row g-0');
  const panellCarpetes = crearElement('aside', 'col-12 col-md-3 col-xl-2 border-end');
  const panellMissatges = crearElement('section', 'col-12 col-md-4 col-xl-4 border-end');
  const panellLectura = crearElement('section', 'col-12 col-md-5 col-xl-6');

  const titolCarpeta = crearElement('h6', 'px-3 pt-3 pb-2 mb-0', 'Safata d’entrada');
  const carpetes = crearElement('nav', 'list-group list-group-flush');

  panellCarpetes.append(titolCarpeta, carpetes);

  const capcaleraMissatges = crearElement('div', 'd-flex justify-content-between align-items-center gap-2 p-3 border-bottom');

  const titolLlista = crearElement('h6', 'mb-0', 'Missatges');
  const cerca = crearElement('input', 'form-control form-control-sm') as HTMLInputElement;
  cerca.type = 'search';
  cerca.placeholder = 'Cercar...';
  cerca.setAttribute('aria-label', 'Cercar missatges');

  capcaleraMissatges.append(titolLlista, cerca);

  const llista = crearElement('div', 'list-group list-group-flush');
  panellMissatges.append(capcaleraMissatges, llista);

  const lectura = crearElement('div', 'p-3');
  mostrarEstat(lectura, 'Selecciona un missatge per llegir-lo.');

  panellLectura.append(lectura);

  contingut.append(panellCarpetes, panellMissatges, panellLectura);
  principal.append(capcalera, contingut);
  contenidor.appendChild(principal);

  return {
    carpetes,
    llista,
    lectura,
    titolCarpeta,
    cerca,
    botoActualitzar,
  };
}

const carpetesObertes = new Set<string>();

function renderitzarCarpetes(contenidor: HTMLElement, carpetes: CarpetaCorreu[], titolCarpeta: HTMLElement, carregar: (carpeta: CarpetaCorreu) => void): void {
  contenidor.replaceChildren();

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

  const renderitzar = (carpeta: CarpetaCorreu, nivell: number): void => {
    const subcarpetes = fills.get(carpeta.ruta) ?? [];
    const teFills = subcarpetes.length > 0;
    const oberta = carpetesObertes.has(carpeta.ruta);

    const fila = crearElement('div', 'd-flex align-items-stretch');

    const boto = crearElement('button', 'list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 rounded-0 flex-grow-1');

    boto.type = 'button';
    boto.style.paddingLeft = `${12 + nivell * 18}px`;

    const esquerra = crearElement('span', 'd-flex align-items-start gap-2 flex-grow-1');

    esquerra.style.minWidth = '0';
    const icona = crearElement('i', `bi ${ICONES_CARPETES[carpeta.tipus]}`);
    const nom = crearElement('span', `text-break ${nivell === 0 ? 'fw-bold' : ''}`, carpeta.nom_visual);

    esquerra.append(icona, nom);
    boto.appendChild(esquerra);

    if (carpeta.missatges_no_llegits > 0) {
      boto.appendChild(crearElement('span', 'badge text-bg-primary rounded-pill', String(carpeta.missatges_no_llegits)));
    }

    boto.classList.toggle('active', carpeta.ruta === carpetaActual);

    boto.addEventListener('click', () => {
      titolCarpeta.textContent = carpeta.nom_visual;
      carregar(carpeta);
    });

    fila.appendChild(boto);

    if (teFills) {
      const botoDesplegar = crearElement('button', 'btn btn-sm btn-outline-secondary border-0 rounded-0 flex-shrink-0');

      botoDesplegar.type = 'button';
      botoDesplegar.setAttribute('aria-label', oberta ? 'Plegar carpeta' : 'Desplegar carpeta');

      const iconaDesplegar = crearElement('span', 'small', oberta ? '▼' : '▶');

      botoDesplegar.appendChild(iconaDesplegar);

      botoDesplegar.addEventListener('click', () => {
        if (oberta) {
          carpetesObertes.delete(carpeta.ruta);
        } else {
          carpetesObertes.add(carpeta.ruta);
        }

        renderitzarCarpetes(contenidor, carpetes, titolCarpeta, carregar);
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

function renderitzarMissatges(contenidor: HTMLElement, missatges: MissatgeLlista[], obrir: (missatge: MissatgeLlista) => void, filtre = ''): void {
  contenidor.replaceChildren();

  const terme = filtre.trim().toLocaleLowerCase();

  const filtrats = missatges.filter((missatge) => {
    return missatge.assumpte.toLocaleLowerCase().includes(terme) || missatge.remitent.email.toLocaleLowerCase().includes(terme) || missatge.remitent.nom.toLocaleLowerCase().includes(terme);
  });

  if (filtrats.length === 0) {
    mostrarEstat(contenidor, 'No hi ha missatges.');
    return;
  }

  for (const missatge of filtrats) {
    const boto = crearElement('button', 'list-group-item list-group-item-action text-start py-3');

    boto.type = 'button';

    const capcalera = crearElement('div', 'd-flex justify-content-between align-items-center gap-2 mb-1');

    const remitent = crearElement('span', missatge.llegit ? 'text-body' : 'fw-bold', missatge.remitent.nom || missatge.remitent.email);

    const data = crearElement('small', 'text-body-secondary text-nowrap', formatarData(missatge.data));

    capcalera.append(remitent, data);

    const assumpte = crearElement('div', missatge.llegit ? 'text-body-secondary' : 'fw-semibold', missatge.assumpte || '(Sense assumpte)');

    boto.append(capcalera, assumpte);

    const icones = crearElement('div', 'd-flex gap-2 mt-1 text-body-secondary');

    if (missatge.te_adjunts) {
      icones.appendChild(crearElement('i', 'bi bi-paperclip'));
    }

    if (missatge.destacat) {
      icones.appendChild(crearElement('i', 'bi bi-star-fill text-warning'));
    }

    if (icones.childElementCount > 0) {
      boto.appendChild(icones);
    }

    boto.addEventListener('click', () => obrir(missatge));

    contenidor.appendChild(boto);
  }
}

function renderitzarLectura(contenidor: HTMLElement, missatge: MissatgeDetall): void {
  contenidor.replaceChildren();

  const capcalera = crearElement('div', 'border-bottom pb-3 mb-3');

  const assumpte = crearElement('h5', 'mb-3', missatge.assumpte || '(Sense assumpte)');

  const remitent = crearElement('div', 'fw-semibold', `${missatge.remitent.nom} <${missatge.remitent.email}>`);

  const destinataris = crearElement('div', 'small text-body-secondary mt-1', `Per a: ${missatge.destinataris.map((d) => d.email).join(', ')}`);

  const data = crearElement('div', 'small text-body-secondary mt-1', formatarData(missatge.data));

  capcalera.append(assumpte, remitent, destinataris, data);

  const cos = crearElement('div', 'correu-contingut');

  if (missatge.contingut_html) {
    const html = DOMPurify.sanitize(missatge.contingut_html, {
      USE_PROFILES: { html: true },
    });

    const contingut = crearElement('div', 'correu-contingut-html');
    contingut.innerHTML = html;

    cos.appendChild(contingut);
  } else if (missatge.contingut_text) {
    const text = crearElement('pre', 'text-wrap mb-0');
    text.textContent = missatge.contingut_text;
    cos.appendChild(text);
  } else {
    cos.textContent = 'Aquest missatge no té contingut disponible.';
  }

  contenidor.append(capcalera, cos);
}

export async function inicialitzarGestorCorreu(): Promise<void> {
  const contenidor = document.getElementById('gestorCorreu');

  if (!contenidor) {
    return;
  }

  const ui = crearInterficie(contenidor);

  const carregarMissatges = async (carpeta: string): Promise<void> => {
    carpetaActual = carpeta;
    //missatgeActual = null;

    mostrarEstat(ui.llista, 'Carregant missatges...');
    mostrarEstat(ui.lectura, 'Selecciona un missatge per llegir-lo.');

    try {
      const resposta = await apiSymfony.get<{
        missatges: MissatgeLlista[];
      }>(ENDPOINT_MISSATGES, {
        carpeta,
        limit: 50,
        offset: 0,
      });

      console.log('RESPOSTA MISSATGES:', resposta);

      missatgesActuals = resposta.missatges;

      renderitzarMissatges(ui.llista, missatgesActuals, obrirMissatge, ui.cerca.value);
    } catch (error) {
      console.error(error);
      mostrarEstat(ui.llista, 'No s’han pogut carregar els missatges.');
    }
  };

  const obrirMissatge = async (missatge: MissatgeLlista): Promise<void> => {
    mostrarEstat(ui.lectura, 'Carregant missatge...');

    try {
      const detall = await apiSymfony.get<MissatgeDetall>(ENDPOINT_MISSATGE, {
        carpeta: missatge.carpeta,
        uid: missatge.id,
        uidValidity: missatge.uid_validity,
      });

      //missatgeActual = detall;
      renderitzarLectura(ui.lectura, detall);

      if (!missatge.llegit) {
        await apiSymfony.patch(ENDPOINT_LLEGIT, {
          carpeta: missatge.carpeta,
          uid: missatge.id,
          uidValidity: missatge.uid_validity,
          llegit: true,
        });

        missatge.llegit = true;
        renderitzarMissatges(ui.llista, missatgesActuals, obrirMissatge, ui.cerca.value);
      }
    } catch (error) {
      console.error(error);
      mostrarEstat(ui.lectura, 'No s’ha pogut obrir el missatge.');
    }
  };

  ui.cerca.addEventListener('input', () => {
    renderitzarMissatges(ui.llista, missatgesActuals, obrirMissatge, ui.cerca.value);
  });

  ui.botoActualitzar.addEventListener('click', () => {
    void carregarMissatges(carpetaActual);
  });

  mostrarEstat(ui.carpetes, 'Carregant carpetes...');

  try {
    const resposta = await apiSymfony.get<RespostaCarpetes>(ENDPOINT_CARPETES);
    const carpetes = resposta.carpetes;

    renderitzarCarpetes(ui.carpetes, carpetes, ui.titolCarpeta, (carpeta) => {
      void carregarMissatges(carpeta.ruta);
    });

    const inbox = carpetes.find((carpeta) => carpeta.tipus === 'inbox');

    if (inbox) {
      ui.titolCarpeta.textContent = inbox.nom;
      await carregarMissatges(inbox.ruta);
    } else {
      mostrarEstat(ui.llista, 'No s’ha trobat la safata d’entrada.');
    }
  } catch (error) {
    console.error(error);
    mostrarEstat(ui.carpetes, 'No s’han pogut carregar les carpetes.');
  }
}
