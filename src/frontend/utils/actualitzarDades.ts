import { formDataToObject } from '../Presentation/Utils/formDataToObject';
import { Missatges } from './locales/missatges';
import { missatgesBackend } from '../Presentation/Utils/missatgesBackend';
import { resetForm } from '../Presentation/Utils/resetForm';
import { clearFieldError, markInvalidFields } from '../Presentation/Utils/formErrors';

type SuccessBehavior = 'none' | 'hide' | 'disable';

type ApiResponse = {
  success?: boolean;
  status?: string;
  message?: string;
  errors?: unknown;
  [key: string]: unknown;
};

function getElements() {
  const okMessageDiv = document.getElementById('okMessage');
  const okTextDiv = document.getElementById('okText');
  const errMessageDiv = document.getElementById('errMessage');
  const errTextDiv = document.getElementById('errText');

  if (!okMessageDiv || !okTextDiv || !errMessageDiv || !errTextDiv) {
    throw new Error('Missing UI message containers');
  }

  return { okMessageDiv, okTextDiv, errMessageDiv, errTextDiv };
}

/**
 * Registra el listener para limpiar los errores de los campos
 * cuando el usuario empieza a corregirlos.
 */
function setupFieldErrorListeners(form: HTMLFormElement): void {
  if (form.dataset.fieldErrorListener === 'true') {
    return;
  }

  form.addEventListener('input', (event: Event) => {
    const target = event.target;

    if (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement) {
      const hadError = target.classList.contains('is-invalid');

      clearFieldError(form, target);

      // Ocultar el mensaje general solo si se ha corregido
      // un campo inválido y ya no quedan otros campos inválidos.
      if (hadError && !form.querySelector('.is-invalid')) {
        const errMessage = document.getElementById('errMessage');

        if (errMessage) {
          errMessage.classList.add('d-none');
        }
      }
    }
  });

  form.addEventListener('change', (event: Event) => {
    const target = event.target;

    if (target instanceof HTMLSelectElement) {
      const hadError = target.classList.contains('is-invalid');

      clearFieldError(form, target);

      if (hadError && !form.querySelector('.is-invalid')) {
        const errMessage = document.getElementById('errMessage');

        if (errMessage) {
          errMessage.classList.add('d-none');
        }
      }
    }
  });

  form.dataset.fieldErrorListener = 'true';
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
  const data: unknown = await res.json().catch(() => ({}));

  if (typeof data === 'object' && data !== null) {
    return data as ApiResponse;
  }

  return {};
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
        const data: unknown = JSON.parse(xhr.responseText || '{}');

        if (typeof data !== 'object' || data === null) {
          reject(new Error('Invalid server response'));
          return;
        }

        if (xhr.status >= 200 && xhr.status < 300) {
          resolve(data as ApiResponse);
        } else {
          reject(data);
        }
      } catch (error: unknown) {
        reject(error);
      }
    };

    xhr.onerror = () => reject({ message: Missatges.error.xarxa });

    xhr.send(formData);
  });
}

/**
 * MAIN FUNCTION
 */
export async function transmissioDadesDB(event: Event, method: string, formId: string, url: string, neteja: boolean = true, successBehavior: SuccessBehavior = 'none', preProcessFormData?: (data: Record<string, unknown>) => Record<string, unknown>, forceMultipart: boolean = false): Promise<ApiResponse | void> {
  event.preventDefault();

  const formElement = document.getElementById(formId);

  if (!(formElement instanceof HTMLFormElement)) {
    console.error(`Form ${formId} not found`);
    return;
  }

  const form = formElement;

  setupFieldErrorListeners(form);

  const ui = getElements();

  try {
    const hasFile = form.querySelector('input[type="file"]') !== null;
    const useMultipart = hasFile || forceMultipart;

    let response: ApiResponse;

    if (useMultipart) {
      response = await uploadWithProgress(method, url, form);
    } else {
      let data = formDataToObject(form);

      if (preProcessFormData) {
        data = preProcessFormData(data);
      }

      response = await request(method, url, data);
    }

    if (response.success) {
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
      missatgesBackend({
        tipus: 'error',
        missatge: response.message || Missatges.error.validation,
        contenidor: ui.errMessageDiv,
        text: ui.errTextDiv,
        altreContenidor: ui.okMessageDiv,
      });

      markInvalidFields(form, response.errors);
    }

    return response;
  } catch (error: unknown) {
    const errors = typeof error === 'object' && error !== null && 'errors' in error ? error.errors : undefined;

    let errorDetails = '';

    if (errors && typeof errors === 'object') {
      errorDetails = Object.values(errors)
        .flatMap((value) => {
          if (typeof value === 'string') {
            return [value];
          }

          if (Array.isArray(value)) {
            return value.filter((item): item is string => typeof item === 'string');
          }

          return [];
        })
        .join('<br>');
    }

    const fullMessage = error instanceof Error ? error.message : typeof error === 'object' && error !== null && 'message' in error && typeof error.message === 'string' ? error.message : Missatges.error.xarxa;

    missatgesBackend({
      tipus: 'error',
      missatge: errorDetails ? `${fullMessage}<div class="mt-2">${errorDetails}</div>` : fullMessage,
      contenidor: ui.errMessageDiv,
      text: ui.errTextDiv,
      altreContenidor: ui.okMessageDiv,
    });

    throw error;
  }
}
