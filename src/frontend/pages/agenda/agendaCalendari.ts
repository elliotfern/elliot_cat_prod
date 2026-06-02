import { api } from '../../core/api/client';

export type TipusEsdeveniment = 'reunio' | 'visita_medica' | 'videotrucada' | 'viatge' | 'altre' | 'aniversari';

export interface BaseAgendaItem {
  id: string;
  titol: string;
  tipus: TipusEsdeveniment;
  dataInici: string;
  dataFi: string | null;
  totElDia: boolean;
  lloc: string | null;
  source: 'agenda' | 'birthday';
}

export type AgendaEsdeveniment = BaseAgendaItem;

const MONTHS_CA = ['Gener', 'Febrer', 'Març', 'Abril', 'Maig', 'Juny', 'Juliol', 'Agost', 'Setembre', 'Octubre', 'Novembre', 'Desembre'];

const WEEK_START_MONDAY = true;

/* =========================
   HELPERS FECHA
========================= */

function parseDateTime(str: string): Date {
  return new Date(str.replace(' ', 'T'));
}

function getDayKey(dateStr: string): string {
  const d = parseDateTime(dateStr);
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

function getMonthRange(year: number, month: number) {
  const from = new Date(year, month, 1);
  const to = new Date(year, month + 1, 0);

  return {
    from: `${from.getFullYear()}-${String(from.getMonth() + 1).padStart(2, '0')}-${String(from.getDate()).padStart(2, '0')}`,
    to: `${to.getFullYear()}-${String(to.getMonth() + 1).padStart(2, '0')}-${String(to.getDate()).padStart(2, '0')}`,
  };
}

function getMonthTitle(year: number, month: number): string {
  return `Agenda ${MONTHS_CA[month]} ${year}`;
}

function getWeekDayIndex(date: Date): number {
  const d = date.getDay();
  return WEEK_START_MONDAY ? (d + 6) % 7 : d;
}

/* =========================
   UI HELPERS
========================= */

function getEventClass(tipus: string): string {
  switch (tipus) {
    case 'aniversari':
      return 'cal-day-event cal-day-event--aniversari';
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

function getShortEventLabel(ev: AgendaEsdeveniment): string {
  const d = parseDateTime(ev.dataInici);

  const hh = String(d.getHours()).padStart(2, '0');
  const mm = String(d.getMinutes()).padStart(2, '0');

  const hora = ev.totElDia ? '' : `${hh}:${mm} · `;
  return `${hora}${ev.titol}`;
}

/* =========================
   API
========================= */

async function loadMonthData(usuariId: number, year: number, month: number): Promise<AgendaEsdeveniment[]> {
  const { from, to } = getMonthRange(year, month);

  const data = await api.get<AgendaEsdeveniment[]>('agenda/get/esdevenimentsRang', { usuari_id: usuariId, from, to });

  return data;
}

/* =========================
   RENDER CALENDAR
========================= */

function renderCalendar(year: number, month: number, events: AgendaEsdeveniment[]) {
  const grid = document.getElementById('cal-grid');
  const title = document.getElementById('cal-month-title');
  if (!grid || !title) return;

  title.textContent = getMonthTitle(year, month);
  grid.innerHTML = '';

  const first = new Date(year, month, 1);
  const last = new Date(year, month + 1, 0);

  const days = last.getDate();
  const startIndex = getWeekDayIndex(first);

  const today = new Date();
  const todayKey = getDayKey(`${today.getFullYear()}-${today.getMonth() + 1}-${today.getDate()}`);

  const byDay: Record<string, AgendaEsdeveniment[]> = {};
  events.forEach((e) => {
    const k = getDayKey(e.dataInici);
    (byDay[k] ??= []).push(e);
  });

  for (let i = 0; i < startIndex; i++) {
    const empty = document.createElement('div');
    empty.className = 'cal-day cal-day--empty';
    grid.appendChild(empty);
  }

  for (let d = 1; d <= days; d++) {
    const date = new Date(year, month, d);
    const key = getDayKey(`${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}`);

    const cell = document.createElement('div');
    cell.className = 'cal-day';

    if (key === todayKey) cell.classList.add('cal-day--today');

    const header = document.createElement('div');
    header.className = 'cal-day-header';

    header.innerHTML = `
      <div class="cal-day-number">${d}</div>
      <div class="cal-day-date-pill">${String(d).padStart(2, '0')}/${String(month + 1).padStart(2, '0')}</div>
    `;

    cell.appendChild(header);

    const container = document.createElement('div');
    container.className = 'cal-day-events';

    const dayEvents = byDay[key] || [];

    dayEvents
      .sort((a, b) => parseDateTime(a.dataInici).getTime() - parseDateTime(b.dataInici).getTime())
      .slice(0, 3)
      .forEach((ev) => {
        const div = document.createElement('div');
        div.className = getEventClass(ev.tipus);

        const a = document.createElement('a');

        a.href = ev.source === 'birthday' ? `/gestio/agenda-contactes/fitxa-contacte/${ev.id}` : `/gestio/agenda/veure-esdeveniment/${ev.id}`;

        a.textContent = getShortEventLabel(ev);
        div.appendChild(a);
        container.appendChild(div);
      });

    cell.appendChild(container);
    grid.appendChild(cell);
  }
}

/* =========================
   INIT
========================= */

export function initCalendariAgenda(): void {
  const grid = document.getElementById('cal-grid');
  if (!grid) return;

  let current = new Date();
  const usuariId = 1;

  async function refresh() {
    try {
      const data = await loadMonthData(usuariId, current.getFullYear(), current.getMonth());

      renderCalendar(current.getFullYear(), current.getMonth(), data);
    } catch (e) {
      console.error(e);
      if (!grid) return;
      grid.innerHTML = '<p>Error carregant el calendari.</p>';
    }
  }

  document.getElementById('cal-prev')?.addEventListener('click', () => {
    current = new Date(current.getFullYear(), current.getMonth() - 1, 1);
    void refresh();
  });

  document.getElementById('cal-next')?.addEventListener('click', () => {
    current = new Date(current.getFullYear(), current.getMonth() + 1, 1);
    void refresh();
  });

  void refresh();
}
