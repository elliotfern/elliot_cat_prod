import { Missatges } from './locales/missatges';
import { missatgesBackend } from './missatgesBackend';
import { resetForm } from './resetForm';

type SuccessBehavior = 'none' | 'hide' | 'disable';

function renderErrors(errors: unknown): string {
  if (!errors) return '';

  if (Array.isArray(errors)) {
    if (errors.length === 0) return '';
    return `<ul>${errors.map((e) => `<li>${String(e)}</li>`).join('')}</ul>`;
  }

  if (typeof errors === 'object') {
    const entries = Object.entries(errors as Record<string, unknown>);
    if (entries.length === 0) return '';
    return `<ul>${entries.map(([k, v]) => `<li><strong>${k}</strong>: ${String(v)}</li>`).join('')}</ul>`;
  }

  return `<p>${String(errors)}</p>`;
}

function markInvalidFields(form: HTMLFormElement, errors: unknown) {
  form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));

  if (!errors || typeof errors !== 'object' || Array.isArray(errors)) return;

  const rec = errors as Record<string, unknown>;

  for (const field of Object.keys(rec)) {
    const selector = `#${CSS.escape(field)}, [name="${CSS.escape(field)}"]`;
    const el = form.querySelector<HTMLElement>(selector);
    if (el) el.classList.add('is-invalid');
  }
}

export async function transmissioDadesDB(event: Event, tipus: string, formId: string, urlAjax: string, neteja?: boolean, successBehavior: SuccessBehavior = 'none', preProcessFormData?: (data: Record<string, unknown>) => Record<string, unknown>): Promise<void> {
  event.preventDefault();

  const form = document.getElementById(formId) as HTMLFormElement;
  if (!form) {
    console.error(`Form with id ${formId} not found`);
    return;
  }

  const hasFileInput = form.querySelector('input[type="file"]') !== null;

  let body: BodyInit;
  let headers: HeadersInit = {
    Accept: 'application/json',
  };

  if (hasFileInput) {
    // =========================
    // 🟢 MODE FORMDATA (UPLOADS)
    // =========================
    const formData = new FormData(form);

    // opcional: normalització simple (manté compatibilitat arrays)
    const normalized = new FormData();
    for (const [key, value] of formData.entries()) {
      normalized.append(key, value);
    }

    body = normalized;
    // ❗ NO Content-Type en FormData
  } else {
    // =========================
    // 🔵 MODE JSON (LEGACY)
    // =========================
    const formDataRaw = new FormData(form);
    const formData: Record<string, unknown> = {};

    for (const [rawKey, value] of formDataRaw.entries()) {
      const isArrayKey = rawKey.endsWith('[]');
      const key = isArrayKey ? rawKey.slice(0, -2) : rawKey;

      if (isArrayKey) {
        const arr = (formData[key] as FormDataEntryValue[] | undefined) ?? [];
        arr.push(value);
        formData[key] = arr;
      } else {
        formData[key] = value;
      }
    }

    const finalData = preProcessFormData ? preProcessFormData(formData) : formData;

    body = JSON.stringify(finalData);
    headers['Content-Type'] = 'application/json';
  }

  try {
    const response = await fetch(urlAjax, {
      method: tipus,
      headers,
      body,
    });

    const data = await response.json();

    const okMessageDiv = document.getElementById('okMessage');
    const okTextDiv = document.getElementById('okText');
    const errMessageDiv = document.getElementById('errMessage');
    const errTextDiv = document.getElementById('errText');

    if (!okMessageDiv || !okTextDiv || !errMessageDiv || !errTextDiv) return;

    if (response.ok && data.status === 'success') {
      markInvalidFields(form, null);

      missatgesBackend({
        tipus: 'success',
        missatge: data.message || Missatges.success.default,
        contenidor: okMessageDiv,
        text: okTextDiv,
        altreContenidor: errMessageDiv,
      });

      const method = tipus.toUpperCase();
      const shouldReset = neteja ?? method === 'POST';

      if (successBehavior === 'hide') {
        form.hidden = true;
      } else if (successBehavior === 'disable') {
        form.querySelectorAll<HTMLElement>('input,select,textarea,button,[contenteditable],trix-editor').forEach((el) => el.setAttribute('disabled', 'true'));
      } else if (shouldReset) {
        resetForm(formId);
      }

      const template = (form.dataset as any).successRedirectTemplate as string | undefined;

      if (template && typeof data?.slug === 'string' && data.slug.length > 0) {
        const href = template.replace('{slug}', encodeURIComponent(data.slug));

        const actions = document.getElementById('successActions') || okMessageDiv;

        let btn = document.getElementById('createdViewBtn') as HTMLAnchorElement | null;

        if (!btn) {
          btn = document.createElement('a');
          btn.id = 'createdViewBtn';
          btn.className = 'btn btn-primary mt-2';
          btn.target = '_self';
          btn.rel = 'noopener';
          btn.textContent = 'Veure fitxa';
          actions.appendChild(btn);
        }

        btn.href = href;
      }

      form.dispatchEvent(new CustomEvent('form:success', { detail: data }));
    } else {
      const missatge = `
        ${data.message ? `<p>${data.message}</p>` : ''}
        ${renderErrors(data.errors) || `<p>${Missatges.error.default}</p>`}
      `;

      missatgesBackend({
        tipus: 'error',
        missatge,
        contenidor: errMessageDiv,
        text: errTextDiv,
        altreContenidor: okMessageDiv,
      });

      markInvalidFields(form, data.errors);
    }
  } catch (error) {
    const errMessageDiv = document.getElementById('errMessage');
    const errTextDiv = document.getElementById('errText');

    if (!errMessageDiv || !errTextDiv) return;

    const errorMessage = typeof error === 'object' && error && 'message' in error ? String((error as { message: string }).message) : Missatges.error.xarxa;

    missatgesBackend({
      tipus: 'error',
      missatge: errorMessage,
      contenidor: errMessageDiv,
      text: errTextDiv,
    });
  }
}
