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

  const okMessageDiv = document.getElementById('okMessage');
  const okTextDiv = document.getElementById('okText');
  const errMessageDiv = document.getElementById('errMessage');
  const errTextDiv = document.getElementById('errText');

  if (!okMessageDiv || !okTextDiv || !errMessageDiv || !errTextDiv) return;

  // =========================================================
  // 🟢 UPLOAD CON PROGRESO (IMÁGENES)
  // =========================================================
  if (hasFileInput) {
    const formData = new FormData(form);

    const xhr = new XMLHttpRequest();
    xhr.open(tipus, urlAjax, true);
    xhr.setRequestHeader('Accept', 'application/json');

    const wrap = document.getElementById('uploadProgress') as HTMLElement | null;
    const bar = document.getElementById('uploadProgressBar') as HTMLElement | null;

    if (wrap) wrap.style.display = 'block';

    if (bar) {
      bar.style.width = '0%';
      bar.textContent = '0%';
    }

    // 👉 PROGRESO
    xhr.upload.onprogress = (event) => {
      if (!event.lengthComputable) return;

      const percent = Math.round((event.loaded / event.total) * 100);

      const bar = document.getElementById('uploadProgressBar') as HTMLElement | null;
      if (bar) {
        bar.style.width = `${percent}%`;
        bar.textContent = `${percent}%`;
      }
    };

    xhr.onload = () => {
      const wrap = document.getElementById('uploadProgress') as HTMLElement | null;
      const bar = document.getElementById('uploadProgressBar') as HTMLElement | null;

      if (bar) {
        bar.style.width = '100%';
        bar.textContent = '100%';
      }

      setTimeout(() => {
        if (wrap) wrap.style.display = 'none';
        if (bar) {
          bar.style.width = '0%';
          bar.textContent = '0%';
        }
      }, 500);

      const data = JSON.parse(xhr.responseText);

      if (xhr.status >= 200 && xhr.status < 300 && data.status === 'success') {
        markInvalidFields(form, null);

        missatgesBackend({
          tipus: 'success',
          missatge: data.message || Missatges.success.default,
          contenidor: okMessageDiv,
          text: okTextDiv,
          altreContenidor: errMessageDiv,
        });

        if (successBehavior === 'hide') {
          form.hidden = true;
        } else if (successBehavior === 'disable') {
          form.querySelectorAll('input,select,textarea,button,[contenteditable],trix-editor').forEach((el) => el.setAttribute('disabled', 'true'));
        } else if (neteja ?? true) {
          resetForm(formId);
        }

        form.dispatchEvent(new CustomEvent('form:success', { detail: data }));
      } else {
        missatgesBackend({
          tipus: 'error',
          missatge: data.message || Missatges.error.default,
          contenidor: errMessageDiv,
          text: errTextDiv,
          altreContenidor: okMessageDiv,
        });

        markInvalidFields(form, data.errors);
      }
    };

    xhr.onerror = () => {
      missatgesBackend({
        tipus: 'error',
        missatge: Missatges.error.xarxa,
        contenidor: errMessageDiv,
        text: errTextDiv,
        altreContenidor: okMessageDiv,
      });
    };

    xhr.send(formData);
    return;
  }

  // =========================================================
  // 🔵 JSON NORMAL (SIN IMÁGENES)
  // =========================================================
  let body: BodyInit;
  let headers: HeadersInit = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  };

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

  try {
    const response = await fetch(urlAjax, {
      method: tipus,
      headers,
      body,
    });

    const data = await response.json();

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
        form.querySelectorAll('input,select,textarea,button,[contenteditable],trix-editor').forEach((el) => el.setAttribute('disabled', 'true'));
      } else if (shouldReset) {
        resetForm(formId);
      }

      form.dispatchEvent(new CustomEvent('form:success', { detail: data }));
    } else {
      missatgesBackend({
        tipus: 'error',
        missatge: data.message || Missatges.error.default,
        contenidor: errMessageDiv,
        text: errTextDiv,
        altreContenidor: okMessageDiv,
      });

      markInvalidFields(form, data.errors);
    }
  } catch (error) {
    missatgesBackend({
      tipus: 'error',
      missatge: Missatges.error.xarxa,
      contenidor: errMessageDiv,
      text: errTextDiv,
    });
  }
}
