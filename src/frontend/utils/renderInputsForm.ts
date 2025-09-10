import { formatDatesForm } from './dates';

export function renderFormInputs<T extends Record<string, unknown>>(data: T): void {
  for (const [key, value] of Object.entries(data)) {
    const input = document.querySelector<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>(`#${key}`);
    if (!input) continue;

    // --- CASE: checkbox ---
    if (input instanceof HTMLInputElement && input.type === 'checkbox') {
      (input as HTMLInputElement).checked = value === true || value === 1 || value === '1' || value === 'on';
      continue;
    }

    // --- CASE: date string (YYYY-MM-DD) ---
    if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value)) {
      const formatted = formatDatesForm(value);
      input.value = formatted ?? '';
      continue;
    }

    // --- CASE: null / undefined ---
    if (value === null || value === undefined) {
      input.value = '';
      continue;
    }

    // --- DEFAULT: anything else as string ---
    input.value = String(value);
  }
}
