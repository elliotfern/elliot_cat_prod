import { Missatges } from './locales/missatges';
import { missatgesBackend } from './missatgesBackend';
import { resetForm } from './resetForm';

type SuccessBehavior = 'none' | 'hide' | 'disable';

type ApiResponse = {
  status?: string;
  message?: string;
  errors?: unknown;
  [key: string]: unknown;
};

function getElements(form: HTMLFormElement) {
  const okMessageDiv = document.getElementById('okMessage');
  const okTextDiv = document.getElementById('okText');
  const errMessageDiv = document.getElementById('errMessage');
  const errTextDiv = document.getElementById('errText');

  if (!okMessageDiv || !okTextDiv || !errMessageDiv || !errTextDiv) {
    throw new Error('Missing UI message containers');
  }

  return { okMessageDiv, okTextDiv, errMessageDiv, errTextDiv };
}

function markInvalidFields(form: HTMLFormElement, errors: unknown) {
  form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));

  if (!errors || typeof errors !== 'object' || Array.isArray(errors)) return;

  const rec = errors as Record<string, unknown>;

  for (const field of Object.keys(rec)) {
    const el = form.querySelector<HTMLElement>(`#${CSS.escape(field)}, [name="${CSS.escape(field)}"]`);
    if (el) el.classList.add('is-invalid');
  }
}

/**
 * Normaliza FormData -> object
 */
function formDataToObject(form: HTMLFormElement): Record<string, unknown> {
  const formData = new FormData(form);
  const data: Record<string, unknown> = {};

  for (const [key, value] of formData.entries()) {
    const isArray = key.endsWith('[]');
    const cleanKey = isArray ? key.replace('[]', '') : key;

    if (isArray) {
      const arr = (data[cleanKey] as unknown[] | undefined) ?? [];
      arr.push(value);
      data[cleanKey] = arr;
    } else {
      data[cleanKey] = value;
    }
  }

  return data;
}

/**
 * REQUEST UNIFICADA
 */
async function request(method: string, url: string, body: FormData | Record<string, unknown>): Promise<ApiResponse> {
  const isFormData = body instanceof FormData;

  const options: RequestInit = {
    method,
    headers: isFormData
      ? { Accept: 'application/json' }
      : {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
    body: isFormData ? body : JSON.stringify(body),
  };

  const res = await fetch(url, options);

  const data = await res.json().catch(() => ({}));

  if (!res.ok) {
    throw data;
  }

  return data;
}

/**
 * UPLOAD CON PROGRESO (XHR SOLO PARA FILES)
 */
function uploadWithProgress(method: string, url: string, form: HTMLFormElement): Promise<ApiResponse> {
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    const formData = new FormData(form);

    xhr.open(method, url, true);
    xhr.setRequestHeader('Accept', 'application/json');

    xhr.onload = () => {
      try {
        const data = JSON.parse(xhr.responseText || '{}');
        if (xhr.status >= 200 && xhr.status < 300) {
          resolve(data);
        } else {
          reject(data);
        }
      } catch (e) {
        reject(e);
      }
    };

    xhr.onerror = () => reject({ message: Missatges.error.xarxa });

    xhr.send(formData);
  });
}

/**
 * MAIN FUNCTION
 */
export async function transmissioDadesDB(event: Event, method: string, formId: string, url: string, neteja: boolean = true, successBehavior: SuccessBehavior = 'none', preProcessFormData?: (data: Record<string, unknown>) => Record<string, unknown>): Promise<ApiResponse | void> {
  event.preventDefault();

  const form = document.getElementById(formId) as HTMLFormElement;
  if (!form) {
    console.error(`Form ${formId} not found`);
    return;
  }

  const ui = getElements(form);

  try {
    const hasFile = form.querySelector('input[type="file"]') !== null;

    let response: ApiResponse;

    if (hasFile) {
      response = await uploadWithProgress(method, url, form);
    } else {
      let data = formDataToObject(form);

      if (preProcessFormData) {
        data = preProcessFormData(data);
      }

      response = await request(method, url, data);
    }

    if (response?.status === 'success') {
      markInvalidFields(form, null);

      missatgesBackend({
        tipus: 'success',
        missatge: response.message || Missatges.success.default,
        contenidor: ui.okMessageDiv,
        text: ui.okTextDiv,
        altreContenidor: ui.errMessageDiv,
      });

      if (successBehavior === 'hide') {
        form.hidden = true;
      } else if (successBehavior === 'disable') {
        form.querySelectorAll('input,select,textarea,button,[contenteditable],trix-editor').forEach((el) => el.setAttribute('disabled', 'true'));
      } else if (neteja) {
        resetForm(formId);
      }

      form.dispatchEvent(new CustomEvent('form:success', { detail: response }));
    } else {
      const errors = response?.errors;

      let errorDetails = '';

      if (errors && typeof errors === 'object' && !Array.isArray(errors)) {
        errorDetails = Object.values(errors).flat().join('<br>');
      }

      missatgesBackend({
        tipus: 'error',
        missatge: errorDetails ? `${response?.message || Missatges.error.default}<div class="mt-2">${errorDetails}</div>` : response?.message || Missatges.error.default,
        contenidor: ui.errMessageDiv,
        text: ui.errTextDiv,
        altreContenidor: ui.okMessageDiv,
      });

      markInvalidFields(form, response?.errors);
    }

    return response;
  } catch (error: any) {
    missatgesBackend({
      tipus: 'error',
      missatge: error?.message || Missatges.error.xarxa,
      contenidor: ui.errMessageDiv,
      text: ui.errTextDiv,
      altreContenidor: ui.okMessageDiv,
    });

    throw error;
  }
}
