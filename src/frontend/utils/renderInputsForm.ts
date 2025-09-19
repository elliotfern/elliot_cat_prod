// src/utils/renderInputsForm.ts

export function renderFormInputs<T extends Record<string, unknown>>(data: T): void {
  const ZERO_UUID = /^0{8}-0{4}-0{4}-0{4}-0{12}$/i;

  const isNil = (v: unknown): boolean => v === null || v === undefined || v === '' || v === 0 || v === '0' || (typeof v === 'string' && ZERO_UUID.test(v));

  for (const [key, value] of Object.entries(data)) {
    // ¡NO usar querySelector(`#${key}`)! Evita '#0' inválidos
    const el = document.getElementById(key) as HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement | null;

    if (!el) continue;

    // --- CHECKBOX ---
    if (el instanceof HTMLInputElement && el.type === 'checkbox') {
      el.checked = value === true || value === 1 || value === '1' || value === 'on';
      continue;
    }

    // --- RADIO (por name) ---
    if (el instanceof HTMLInputElement && el.type === 'radio') {
      const group = document.querySelectorAll<HTMLInputElement>(`input[type="radio"][name="${el.name}"]`);
      const target = String(value ?? '');
      group.forEach((r) => {
        r.checked = String(r.value) === target;
      });
      continue;
    }

    // --- DATE (YYYY-MM-DD) ---
    if (el instanceof HTMLInputElement && el.type === 'date') {
      el.value = typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value) ? value : '';
      continue;
    }

    // --- NUMBER ---
    if (el instanceof HTMLInputElement && el.type === 'number') {
      if (isNil(value)) {
        el.value = '';
      } else {
        const n = typeof value === 'number' ? value : Number(value);
        el.value = Number.isFinite(n) ? String(n) : '';
      }
      continue;
    }

    // --- SELECT (multiple) ---
    if (el instanceof HTMLSelectElement && el.multiple) {
      const values = Array.isArray(value) ? value.map(String).filter((v) => !isNil(v)) : isNil(value) ? [] : [String(value)];

      // Si aún no hay opciones (lo llenará auxiliarSelect), no forzar selección
      if (el.options.length === 0) continue;

      Array.from(el.options).forEach((opt) => {
        opt.selected = values.includes(opt.value);
      });
      el.dispatchEvent(new Event('change', { bubbles: true }));
      continue;
    }

    // --- SELECT (single) ---
    if (el instanceof HTMLSelectElement) {
      // Si aún no hay opciones (lo llenará auxiliarSelect), no forzar selección
      if (el.options.length === 0) continue;

      const v = isNil(value) ? '' : String(value);
      if (v && !Array.from(el.options).some((o) => o.value === v)) {
        el.value = ''; // si no existe opción con ese value, no hacemos nada raro
      } else {
        el.value = v;
      }
      el.dispatchEvent(new Event('change', { bubbles: true }));
      continue;
    }

    // --- DEFAULT: inputs de texto / textarea ---
    (el as HTMLInputElement | HTMLTextAreaElement).value = isNil(value) ? '' : String(value);
  }
}
