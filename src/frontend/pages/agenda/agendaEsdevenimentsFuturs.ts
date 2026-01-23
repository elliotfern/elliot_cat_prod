// models/AgendaEsdeveniment.ts
export type TipusEsdeveniment = 'reunio' | 'visita_medica' | 'videotrucada' | 'altre' | 'viatge';
export type EstatEsdeveniment = 'pendent' | 'confirmat' | 'cancel·lat' | 'cancel-lat';

export interface AgendaEsdeveniment {
  id_esdeveniment: number;
  titol: string;
  descripcio?: string | null;
  tipus: TipusEsdeveniment;
  lloc?: string | null;
  url_videotrucada?: string | null;
  data_inici: string; // "2025-12-05 10:00:00"
  data_fi: string; // "2025-12-05 11:00:00"
  tot_el_dia: number; // 0 | 1
  estat: EstatEsdeveniment;
  creat_el: string;
  actualitzat_el: string;
}

const MONTHS_CA = ['Gener', 'Febrer', 'Març', 'Abril', 'Maig', 'Juny', 'Juliol', 'Agost', 'Setembre', 'Octubre', 'Novembre', 'Desembre'];

interface ApiResponse<T> {
  success?: boolean;
  message?: string;
  data?: T;
  // ajusta si tu Response::success usa otras claves
}

/**
 * Parsea "YYYY-MM-DD HH:MM:SS" a Date
 */
function parseDateTime(dateTimeStr: string): Date {
  // Reemplazamos espacio por 'T' para que el Date lo entienda mejor
  return new Date(dateTimeStr.replace(' ', 'T'));
}

/**
 * Formatea fecha/hora para mostrar en la línea meta
 */
function formatEventDate(ev: AgendaEsdeveniment): string {
  const start = parseDateTime(ev.data_inici);
  const end = parseDateTime(ev.data_fi);

  const pad = (n: number) => (n < 10 ? `0${n}` : `${n}`);

  const day = pad(start.getDate());
  const month = pad(start.getMonth() + 1);
  const year = start.getFullYear();

  if (ev.tot_el_dia) {
    // Evento de todo el día
    return `Tot el dia · ${day}/${month}/${year}`;
  }

  const hStart = pad(start.getHours());
  const mStart = pad(start.getMinutes());
  const hEnd = pad(end.getHours());
  const mEnd = pad(end.getMinutes());

  return `${day}/${month}/${year} · ${hStart}:${mStart} - ${hEnd}:${mEnd}`;
}

/**
 * Genera clave de agrupación "YYYY-MM"
 */
function getMonthKey(dateStr: string): string {
  const d = parseDateTime(dateStr);
  const year = d.getFullYear();
  const month = d.getMonth(); // 0-11
  return `${year}-${month}`;
}

/**
 * Devuelve "Agenda Desembre 2025"
 */
function getMonthTitle(key: string): string {
  const [yearStr, monthStr] = key.split('-');
  const year = parseInt(yearStr, 10);
  const monthIndex = parseInt(monthStr, 10); // 0-11
  const monthName = MONTHS_CA[monthIndex] ?? '';
  return `Agenda ${monthName} ${year}`;
}

/**
 * Clase CSS para el badge de tipus
 */
function getTipusBadgeClass(tipus: string): string {
  switch (tipus) {
    case 'reunio':
      return 'agenda-badge agenda-badge-tipus-reunio';
    case 'visita_medica':
      return 'agenda-badge agenda-badge-tipus-visita_medica';
    case 'videotrucada':
      return 'agenda-badge agenda-badge-tipus-videotrucada';
    case 'viatge':
      return 'agenda-badge agenda-badge-tipus-viatge'; // 👈 nuevo caso
    default:
      return 'agenda-badge agenda-badge-tipus-altre';
  }
}

/**
 * Clase CSS para el badge d'estat
 */
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

/**
 * Carga eventos futuros y pinta la agenda
 */
export async function carregarAgendaFutura(usuariId: number): Promise<void> {
  const container = document.getElementById('agenda-llistat');
  usuariId = 1;
  if (!container) return;

  container.innerHTML = '<p>Carregant agenda...</p>';

  try {
    const res = await fetch(`/api/agenda/get/esdevenimentsFuturs?usuari_id=${usuariId}`, {
      method: 'GET',
      headers: { Accept: 'application/json' },
      credentials: 'include',
    });

    if (!res.ok) {
      container.innerHTML = "<p>Error carregant l'agenda.</p>";
      return;
    }

    const json: ApiResponse<AgendaEsdeveniment[]> | any = await res.json();
    const events: AgendaEsdeveniment[] = json.data ?? json ?? [];

    if (!events.length) {
      container.innerHTML = '<p>No tens esdeveniments futurs programats.</p>';
      return;
    }

    // Agrupar por mes
    const groups: Record<string, AgendaEsdeveniment[]> = {};

    events.forEach((ev) => {
      const key = getMonthKey(ev.data_inici);
      if (!groups[key]) {
        groups[key] = [];
      }
      groups[key].push(ev);
    });

    // Ordenar por mes (ascendente)
    const sortedKeys = Object.keys(groups).sort((a, b) => {
      const [ya, ma] = a.split('-').map(Number);
      const [yb, mb] = b.split('-').map(Number);
      if (ya === yb) return ma - mb;
      return ya - yb;
    });

    // Pintar HTML
    container.innerHTML = '';

    sortedKeys.forEach((key) => {
      const monthEvents = groups[key].sort((a, b) => {
        const da = parseDateTime(a.data_inici).getTime();
        const db = parseDateTime(b.data_inici).getTime();
        return da - db;
      });

      const monthDiv = document.createElement('div');
      monthDiv.className = 'agenda-month';

      const title = document.createElement('h2');
      title.className = 'agenda-month-title';
      title.textContent = getMonthTitle(key);

      const list = document.createElement('ul');
      list.className = 'agenda-event-list';

      monthEvents.forEach((ev) => {
        const item = document.createElement('li');
        item.className = 'agenda-event-item';

        const mainDiv = document.createElement('div');
        mainDiv.className = 'agenda-event-main';

        const titleSpan = document.createElement('div');
        titleSpan.className = 'agenda-event-title';
        titleSpan.textContent = ev.titol;

        const badgesDiv = document.createElement('div');
        badgesDiv.className = 'agenda-badges';

        const tipusBadge = document.createElement('span');
        tipusBadge.className = getTipusBadgeClass(ev.tipus);
        tipusBadge.textContent = ev.tipus === 'reunio' ? 'Reunió' : ev.tipus === 'visita_medica' ? 'Visita mèdica' : ev.tipus === 'videotrucada' ? 'Videotrucada' : ev.tipus === 'viatge' ? 'Viatge' : 'Altres';

        const estatBadge = document.createElement('span');
        estatBadge.className = getEstatBadgeClass(ev.estat);
        estatBadge.textContent = ev.estat === 'pendent' ? 'Pendent' : ev.estat === 'confirmat' ? 'Confirmat' : 'Cancel·lat';

        badgesDiv.appendChild(tipusBadge);
        badgesDiv.appendChild(estatBadge);

        if (ev.lloc) {
          const llocBadge = document.createElement('span');
          llocBadge.className = 'agenda-badge';
          llocBadge.textContent = ev.lloc;
          badgesDiv.appendChild(llocBadge);
        } else if (ev.url_videotrucada) {
          const onlineBadge = document.createElement('span');
          onlineBadge.className = 'agenda-badge';
          onlineBadge.textContent = 'Online';
          badgesDiv.appendChild(onlineBadge);
        }

        mainDiv.appendChild(titleSpan);

        if (ev.descripcio) {
          const desc = document.createElement('div');
          desc.className = 'agenda-event-desc';
          desc.style.fontSize = '0.85rem';
          desc.style.color = '#4b5563';
          desc.textContent = ev.descripcio;
          mainDiv.appendChild(desc);
        }

        mainDiv.appendChild(badgesDiv);

        // META (fecha/hora + botón)
        const metaDiv = document.createElement('div');
        metaDiv.className = 'agenda-event-meta';

        // 1) Fecha/hora en negrita
        const dateSpan = document.createElement('strong');
        dateSpan.textContent = formatEventDate(ev);

        // 2) Botón "Modificar"
        const editBtn = document.createElement('a');
        editBtn.className = 'agenda-btn-modificar';
        editBtn.href = `/gestio/agenda/modifica-esdeveniment/${ev.id_esdeveniment}`;
        editBtn.textContent = 'Modificar';

        metaDiv.appendChild(dateSpan);
        metaDiv.appendChild(editBtn);

        item.appendChild(mainDiv);
        item.appendChild(metaDiv);
        list.appendChild(item);
      });

      monthDiv.appendChild(title);
      monthDiv.appendChild(list);
      container.appendChild(monthDiv);
    });
  } catch (error) {
    console.error(error);
    container.innerHTML = "<p>Error inesperat carregant l'agenda.</p>";
  }
}
