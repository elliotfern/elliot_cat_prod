import { formatDatesForm } from './dates';

export async function renderFormInputs<T extends Record<string, unknown>>(data: T): Promise<void> {
  for (const [key, value] of Object.entries(data)) {
    const input = document.querySelector<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>(`#${key}`);
    if (!input) continue;

    // --- CASE: checkbox ---
    if (input instanceof HTMLInputElement && input.type === 'checkbox') {
      (input as HTMLInputElement).checked = value === true || value === 1 || value === '1' || value === 'on';
      continue;
    }

    // Inputs de tipo "date"
    if (input instanceof HTMLInputElement && input.type === 'date') {
      input.value =
        typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value)
          ? value // usar directamente YYYY-MM-DD
          : '';
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
