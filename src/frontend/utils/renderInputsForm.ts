import { formatDatesForm } from './dates';

async function setTrixHTML(inputId: string, html: string | undefined | null) {
  const safe = html ?? '';

  const hidden = document.getElementById(inputId) as HTMLInputElement | null;
  if (hidden) hidden.value = safe;

  const editorEl = document.querySelector(`trix-editor[input="${inputId}"]`) as any;
  if (!editorEl) return;

  // Si ya existe el editor, cargar directamente
  if (editorEl.editor) {
    editorEl.editor.loadHTML(safe);
    return;
  }

  // Si todavía no existe -> esperar a trix-initialize
  await new Promise<void>((resolve) => {
    editorEl.addEventListener(
      'trix-initialize',
      () => {
        editorEl.editor.loadHTML(safe);
        resolve();
      },
      { once: true }
    );
  });
}

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

    // --- CASE: TRIX hidden input ---
    if (input instanceof HTMLInputElement && input.type === 'hidden' && input.nextElementSibling?.tagName === 'TRIX-EDITOR') {
      await setTrixHTML(input.id, value ? String(value) : '');
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
