// src/Presentation/Utils/renderFormInputs.ts

export function renderFormInputs(data: object): void {
  const ZERO_UUID = /^0{8}-0{4}-0{4}-0{4}-0{12}$/i;

  const isNil = (v: unknown): boolean => v === null || v === undefined || v === '' || (typeof v === 'string' && ZERO_UUID.test(v));

  for (const [key, value] of Object.entries(data)) {
    const el = document.getElementById(key) as HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement | null;

    if (!el) continue;

    // CHECKBOX
    if (el instanceof HTMLInputElement && el.type === 'checkbox') {
      el.checked = value === true || value === 1 || value === '1' || value === 'on';

      continue;
    }

    // RADIO
    if (el instanceof HTMLInputElement && el.type === 'radio') {
      const group = document.querySelectorAll<HTMLInputElement>(`input[type="radio"][name="${el.name}"]`);

      const target = String(value ?? '');

      group.forEach((radio) => {
        radio.checked = String(radio.value) === target;
      });

      continue;
    }

    // DATE
    if (el instanceof HTMLInputElement && el.type === 'date') {
      el.value = typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value) ? value : '';

      continue;
    }

    // NUMBER
    if (el instanceof HTMLInputElement && el.type === 'number') {
      if (isNil(value)) {
        el.value = '';
      } else {
        const n = typeof value === 'number' ? value : Number(value);

        el.value = Number.isFinite(n) ? String(n) : '';
      }

      continue;
    }

    // SELECT MULTIPLE
    if (el instanceof HTMLSelectElement && el.multiple) {
      const values = Array.isArray(value) ? value.map(String).filter((v) => !isNil(v)) : isNil(value) ? [] : [String(value)];

      if (el.options.length === 0) continue;

      Array.from(el.options).forEach((option) => {
        option.selected = values.includes(option.value);
      });

      el.dispatchEvent(new Event('change', { bubbles: true }));

      continue;
    }

    // SELECT
    if (el instanceof HTMLSelectElement) {
      if (el.options.length === 0) continue;

      const v = isNil(value) ? '' : String(value);

      if (v && !Array.from(el.options).some((option) => option.value === v)) {
        el.value = '';
      } else {
        el.value = v;
      }

      el.dispatchEvent(new Event('change', { bubbles: true }));

      continue;
    }

    // INPUT / TEXTAREA
    (el as HTMLInputElement | HTMLTextAreaElement).value = isNil(value) ? '' : String(value);
  }
}
