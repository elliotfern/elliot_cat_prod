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

const MONTHS_CA = ['Gener', 'Febrer', 'Març', 'Abril', 'Maig', 'Juny', 'Juliol', 'Agost', 'Setembre', 'Octubre', 'Novembre', 'Desembre'];

const WEEK_START_MONDAY = true;

interface ApiResponse<T> {
  success?: boolean;
  message?: string;
  data?: T;
}

/** Parsea "YYYY-MM-DD HH:MM:SS" a Date */
function parseDateTime(dateTimeStr: string): Date {
  return new Date(dateTimeStr.replace(' ', 'T'));
}

/** Devuelve "YYYY-MM-DD" para clave de día */
function getDayKey(dateStr: string): string {
  const d = parseDateTime(dateStr);
  const year = d.getFullYear();
  const month = (d.getMonth() + 1).toString().padStart(2, '0');
  const day = d.getDate().toString().padStart(2, '0');
  return `${year}-${month}-${day}`;
}

/** Devuelve rango desde primer día del mes a último día */
function getMonthRange(year: number, monthIndex: number): { from: string; to: string } {
  // monthIndex: 0-11
  const firstDate = new Date(year, monthIndex, 1);
  const lastDate = new Date(year, monthIndex + 1, 0); // día 0 del mes siguiente = último del mes actual

  const from = [firstDate.getFullYear(), (firstDate.getMonth() + 1).toString().padStart(2, '0'), firstDate.getDate().toString().padStart(2, '0')].join('-');

  const to = [lastDate.getFullYear(), (lastDate.getMonth() + 1).toString().padStart(2, '0'), lastDate.getDate().toString().padStart(2, '0')].join('-');

  return { from, to };
}

/** Título mes: "Agenda Desembre 2025" */
function getMonthTitle(year: number, monthIndex: number): string {
  return `Agenda ${MONTHS_CA[monthIndex]} ${year}`;
}

/** Clase para evento en el calendario según tipus */
function getEventClass(tipus: string): string {
  switch (tipus) {
    case 'reunio':
      return 'cal-day-event cal-day-event--reunio';
    case 'visita_medica':
      return 'cal-day-event cal-day-event--visita_medica';
    case 'videotrucada':
      return 'cal-day-event cal-day-event--videotrucada';
    case 'viatge':
      return 'cal-day-event cal-day-event--viatge';
    default:
      return 'cal-day-event cal-day-event--altre';
  }
}

/** Texto corto de evento (por ejemplo "10:30 Podologia") */
function getShortEventLabel(ev: AgendaEsdeveniment): string {
  const d = parseDateTime(ev.data_inici);
  const pad = (n: number) => (n < 10 ? `0${n}` : `${n}`);
  const hh = pad(d.getHours());
  const mm = pad(d.getMinutes());
  const hora = ev.tot_el_dia ? '' : `${hh}:${mm} · `;

  return `${hora}${ev.titol}`;
}

/** Devuelve número de día de la semana (0 = lunes, 6 = domingo) */
function getWeekDayIndex(date: Date): number {
  const day = date.getDay(); // 0 = domingo, 1 = lunes, ...
  if (!WEEK_START_MONDAY) {
    return day;
  }
  // Convertimos para que lunes sea 0
  return (day + 6) % 7; // Lunes=1 -> 0, Martes=2 -> 1, ..., Domingo=0 -> 6
}

/** Dibuja el calendario en el DOM */
function renderCalendar(year: number, monthIndex: number, events: AgendaEsdeveniment[]): void {
  const grid = document.getElementById('cal-grid');
  const title = document.getElementById('cal-month-title');

  if (!grid || !title) return;

  title.textContent = getMonthTitle(year, monthIndex);
  grid.innerHTML = '';

  const firstDate = new Date(year, monthIndex, 1);
  const lastDate = new Date(year, monthIndex + 1, 0);
  const daysInMonth = lastDate.getDate();

  // Hoy para resaltar
  const today = new Date();
  const todayKey = today.getFullYear() + '-' + (today.getMonth() + 1).toString().padStart(2, '0') + '-' + today.getDate().toString().padStart(2, '0');

  // Agrupar eventos por día
  const eventsByDay: Record<string, AgendaEsdeveniment[]> = {};
  events.forEach((ev) => {
    const key = getDayKey(ev.data_inici);
    if (!eventsByDay[key]) {
      eventsByDay[key] = [];
    }
    eventsByDay[key].push(ev);
  });

  // Día de la semana del primer día
  const startIndex = getWeekDayIndex(firstDate);

  // Celdas vacías antes del día 1
  for (let i = 0; i < startIndex; i++) {
    const emptyCell = document.createElement('div');
    emptyCell.className = 'cal-day cal-day--empty';
    grid.appendChild(emptyCell);
  }

  // Celdas del mes
  for (let day = 1; day <= daysInMonth; day++) {
    const cellDate = new Date(year, monthIndex, day);
    const yearStr = cellDate.getFullYear();
    const monthStr = (cellDate.getMonth() + 1).toString().padStart(2, '0');
    const dayStr = cellDate.getDate().toString().padStart(2, '0');
    const dayKey = `${yearStr}-${monthStr}-${dayStr}`;

    const cell = document.createElement('div');
    cell.className = 'cal-day';

    if (dayKey === todayKey) {
      cell.classList.add('cal-day--today');
    }

    // Cabecera de la celda
    const header = document.createElement('div');
    header.className = 'cal-day-header';

    const numberSpan = document.createElement('div');
    numberSpan.className = 'cal-day-number';
    numberSpan.textContent = day.toString();

    const pill = document.createElement('div');
    pill.className = 'cal-day-date-pill';
    pill.textContent = `${dayStr}/${monthStr}`;

    header.appendChild(numberSpan);
    header.appendChild(pill);

    cell.appendChild(header);

    // Contenedor de eventos
    const dayEventsContainer = document.createElement('div');
    dayEventsContainer.className = 'cal-day-events';

    const dayEvents = eventsByDay[dayKey] || [];
    if (dayEvents.length > 0) {
      // ordenar por hora
      dayEvents.sort((a, b) => {
        const ta = parseDateTime(a.data_inici).getTime();
        const tb = parseDateTime(b.data_inici).getTime();
        return ta - tb;
      });

      const maxVisible = 3;
      dayEvents.slice(0, maxVisible).forEach((ev) => {
        const evDiv = document.createElement('div');
        evDiv.className = getEventClass(ev.tipus);

        const link = document.createElement('a');
        link.href = `/gestio/agenda/veure-esdeveniment/${ev.id_esdeveniment}`;
        link.textContent = getShortEventLabel(ev);
        link.title = ev.titol; // tooltip opcional

        evDiv.appendChild(link);
        dayEventsContainer.appendChild(evDiv);
      });

      if (dayEvents.length > maxVisible) {
        const more = document.createElement('div');
        more.className = 'cal-day-more';
        more.textContent = `+${dayEvents.length - maxVisible} més...`;
        dayEventsContainer.appendChild(more);
      }
    }

    cell.appendChild(dayEventsContainer);
    grid.appendChild(cell);
  }
}

/** Carga eventos del mes desde el backend */
async function loadMonthData(usuariId: number, year: number, monthIndex: number): Promise<AgendaEsdeveniment[]> {
  const { from, to } = getMonthRange(year, monthIndex);

  const res = await fetch(`/api/agenda/get/esdevenimentsRang?usuari_id=${usuariId}&from=${from}&to=${to}`, {
    method: 'GET',
    headers: { Accept: 'application/json' },
    credentials: 'include',
  });

  if (!res.ok) {
    throw new Error('Error carregant esdeveniments del mes');
  }

  const json: ApiResponse<AgendaEsdeveniment[]> | any = await res.json();
  const data: AgendaEsdeveniment[] = json.data ?? json ?? [];
  return data;
}

/** Inicializa calendario */
export function initCalendariAgenda(): void {
  const grid = document.getElementById('cal-grid');
  if (!grid) return; // No estamos en la página de calendario

  const prevBtn = document.getElementById('cal-prev');
  const nextBtn = document.getElementById('cal-next');

  // ID de usuario desde el entorno global (ajusta a tu app)
  const usuariId = 1;
  if (!usuariId) {
    console.warn("No s'ha trobat APP_USER_ID per carregar el calendari.");
    return;
  }

  let currentDate = new Date();

  async function refresh() {
    try {
      const year = currentDate.getFullYear();
      const monthIndex = currentDate.getMonth();
      const events = await loadMonthData(usuariId, year, monthIndex);
      renderCalendar(year, monthIndex, events);
    } catch (err) {
      if (!grid) return; // No estamos en la página de calendario
      console.error(err);
      grid.innerHTML = '<p>Error carregant el calendari.</p>';
    }
  }

  prevBtn?.addEventListener('click', () => {
    currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 1);
    void refresh();
  });

  nextBtn?.addEventListener('click', () => {
    currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1);
    void refresh();
  });

  void refresh();
}
