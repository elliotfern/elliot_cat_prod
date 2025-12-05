// models/AgendaEsdeveniment.ts
export type TipusEsdeveniment = 'reunio' | 'visita_medica' | 'videotrucada' | 'viatge' | 'altre';

export type EstatEsdeveniment = 'pendent' | 'confirmat' | 'cancel·lat' | 'cancel-lat';

export interface AgendaEsdeveniment {
  id_esdeveniment: number;
  usuari_id: number;
  titol: string;
  descripcio?: string | null;
  tipus: TipusEsdeveniment;
  lloc?: string | null;
  url_videotrucada?: string | null;
  data_inici: string; // "YYYY-MM-DD HH:MM:SS"
  data_fi: string;
  tot_el_dia: number;
  estat: EstatEsdeveniment;
  creat_el: string;
  actualitzat_el: string;
}

const MONTHS_CA = ['gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];

const WEEKDAYS_CA = ['Diumenge', 'Dilluns', 'Dimarts', 'Dimecres', 'Dijous', 'Divendres', 'Dissabte'];

interface ApiResponse<T> {
  success?: boolean;
  message?: string;
  data?: T;
}

function parseDateTime(dateTimeStr: string): Date {
  return new Date(dateTimeStr.replace(' ', 'T'));
}

/**
 * Formato largo en catalán, por ejemplo:
 * "Dilluns, 22 de desembre de 2025 · 10:30 - 11:30"
 */
function formatLongDate(ev: AgendaEsdeveniment): { principal: string; sub: string } {
  const start = parseDateTime(ev.data_inici);
  const end = parseDateTime(ev.data_fi);

  const pad = (n: number) => (n < 10 ? `0${n}` : `${n}`);

  const weekday = WEEKDAYS_CA[start.getDay()];
  const day = start.getDate();
  const monthName = MONTHS_CA[start.getMonth()];
  const year = start.getFullYear();

  if (ev.tot_el_dia) {
    const principal = `${weekday}, ${day} de ${monthName} de ${year}`;
    const sub = 'Esdeveniment de tot el dia';
    return { principal, sub };
  }

  const hStart = pad(start.getHours());
  const mStart = pad(start.getMinutes());
  const hEnd = pad(end.getHours());
  const mEnd = pad(end.getMinutes());

  const principal = `${weekday}, ${day} de ${monthName} de ${year}`;
  const sub = `${hStart}:${mStart} - ${hEnd}:${mEnd}`;

  return { principal, sub };
}

function getTipusLabel(tipus: string): string {
  switch (tipus) {
    case 'reunio':
      return 'Reunió';
    case 'visita_medica':
      return 'Visita mèdica';
    case 'videotrucada':
      return 'Videotrucada';
    case 'viatge':
      return 'Viatge';
    default:
      return 'Altres';
  }
}

function getTipusBadgeClass(tipus: string): string {
  switch (tipus) {
    case 'reunio':
      return 'agenda-badge agenda-badge-tipus-reunio';
    case 'visita_medica':
      return 'agenda-badge agenda-badge-tipus-visita_medica';
    case 'videotrucada':
      return 'agenda-badge agenda-badge-tipus-videotrucada';
    case 'viatge':
      return 'agenda-badge agenda-badge-tipus-viatge';
    default:
      return 'agenda-badge agenda-badge-tipus-altre';
  }
}

function getEstatLabel(estat: string): string {
  switch (estat) {
    case 'pendent':
      return 'Pendent';
    case 'confirmat':
      return 'Confirmat';
    case 'cancel·lat':
    case 'cancel-lat':
      return 'Cancel·lat';
    default:
      return estat;
  }
}

function getEstatBadgeClass(estat: string): string {
  switch (estat) {
    case 'pendent':
      return 'agenda-badge agenda-badge-estat-pendent';
    case 'confirmat':
      return 'agenda-badge agenda-badge-estat-confirmat';
    case 'cancel·lat':
    case 'cancel-lat':
      return 'agenda-badge agenda-badge-estat-cancel·lat';
    default:
      return 'agenda-badge agenda-badge-estat-pendent';
  }
}

/** Intenta obtener el ID del data-attribute, y si no, de la URL */
function getEsdevenimentIdFromDomOrUrl(wrapper: HTMLElement): number | null {
  const attr = wrapper.getAttribute('data-esdeveniment-id');
  if (attr && /^\d+$/.test(attr)) {
    return parseInt(attr, 10);
  }

  const pathParts = window.location.pathname.split('/').filter(Boolean);
  const last = pathParts[pathParts.length - 1];
  if (last && /^\d+$/.test(last)) {
    return parseInt(last, 10);
  }

  return null;
}

/** Pinta el HTML del evento dentro del wrapper */
function renderEsdeveniment(ev: AgendaEsdeveniment): void {
  const wrapper = document.getElementById('agenda-esdeveniment-main');
  if (!wrapper) return;

  const { principal, sub } = formatLongDate(ev);

  const llocHtml = ev.lloc
    ? `<div class="agenda-detall-section">
         <div class="agenda-detall-label">Lloc</div>
         <div class="agenda-detall-value">${ev.lloc}</div>
       </div>`
    : '';

  const urlHtml = ev.url_videotrucada
    ? `<div class="agenda-detall-section">
         <div class="agenda-detall-label">Videotrucada</div>
         <div class="agenda-detall-value">
           <a href="${ev.url_videotrucada}" target="_blank" rel="noopener noreferrer">
             ${ev.url_videotrucada}
           </a>
         </div>
       </div>`
    : '';

  const descripcioHtml = ev.descripcio
    ? `<div class="agenda-detall-section">
         <div class="agenda-detall-label">Detalls</div>
         <div class="agenda-detall-descripcio">${ev.descripcio}</div>
       </div>`
    : '';

  const creat = parseDateTime(ev.creat_el);
  const actualitzat = parseDateTime(ev.actualitzat_el);

  const metaText = `Creat el ${creat.toLocaleString('ca-ES')} · Última actualització: ${actualitzat.toLocaleString('ca-ES')}`;

  wrapper.innerHTML = `
    <h1 class="agenda-detall-titol">${ev.titol}</h1>

    <div class="agenda-detall-data">${principal}</div>
    <div class="agenda-detall-data-sub">${sub}</div>

    <div class="agenda-detall-badges">
      <span class="${getTipusBadgeClass(ev.tipus)}">${getTipusLabel(ev.tipus)}</span>
      <span class="${getEstatBadgeClass(ev.estat)}">${getEstatLabel(ev.estat)}</span>
      ${ev.tot_el_dia ? '<span class="agenda-badge">Tot el dia</span>' : ''}
    </div>

    ${llocHtml}
    ${urlHtml}
    ${descripcioHtml}

    <div class="agenda-detall-meta">
      <span>${metaText}</span>
    </div>
  `;

  // Actualizamos link del botón "Modificar"
  const btnModificar = document.getElementById('btn-modificar-esdeveniment') as HTMLAnchorElement | null;
  if (btnModificar) {
    btnModificar.href = `/gestio/agenda/modifica-esdeveniment/${ev.id_esdeveniment}`;
  }
}

/** Carga el evento desde la API */
export async function carregarEsdevenimentDetall(): Promise<void> {
  const wrapper = document.getElementById('agenda-esdeveniment-wrapper');
  const main = document.getElementById('agenda-esdeveniment-main');

  if (!wrapper || !main) return;

  const id = getEsdevenimentIdFromDomOrUrl(wrapper);
  if (!id) {
    main.innerHTML = "<p>No s'ha pogut determinar l'ID de l'esdeveniment.</p>";
    return;
  }

  main.innerHTML = '<p>Carregant esdeveniment...</p>';

  try {
    const res = await fetch(`/api/agenda/get/esdevenimentId?id=${id}`, {
      method: 'GET',
      headers: { Accept: 'application/json' },
      credentials: 'include',
    });

    if (!res.ok) {
      main.innerHTML = "<p>Error carregant l'esdeveniment.</p>";
      return;
    }

    const json: ApiResponse<AgendaEsdeveniment> | any = await res.json();
    const ev: AgendaEsdeveniment = (json.data ?? json) as AgendaEsdeveniment;

    if (!ev || !ev.id_esdeveniment) {
      main.innerHTML = '<p>Esdeveniment no trobat.</p>';
      return;
    }

    renderEsdeveniment(ev);
  } catch (err) {
    console.error(err);
    main.innerHTML = "<p>Error inesperat carregant l'esdeveniment.</p>";
  }
}
