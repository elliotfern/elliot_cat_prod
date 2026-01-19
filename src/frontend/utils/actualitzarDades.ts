import { Missatges } from './locales/missatges';
import { missatgesBackend } from './missatgesBackend';
import { resetForm } from './resetForm';

// Comportamiento genérico en éxito
type SuccessBehavior = 'none' | 'hide' | 'disable';

/**
 * Renderiza errors tanto si viene como array (legacy) como si viene como object {field: reason}.
 */
function renderErrors(errors: unknown): string {
  if (!errors) return '';

  // Array legacy: ["msg1", "msg2"] (o cualquier valor)
  if (Array.isArray(errors)) {
    if (errors.length === 0) return '';
    return `<ul>${errors.map((e) => `<li>${String(e)}</li>`).join('')}</ul>`;
  }

  // Object nuevo: { field: "required", ... }
  if (typeof errors === 'object') {
    const entries = Object.entries(errors as Record<string, unknown>);
    if (entries.length === 0) return '';
    return `<ul>${entries.map(([k, v]) => `<li><strong>${k}</strong>: ${String(v)}</li>`).join('')}</ul>`;
  }

  // string u otros
  return `<p>${String(errors)}</p>`;
}

/**
 * Marca campos inválidos (Bootstrap .is-invalid) cuando errors viene como object.
 * Busca primero por id="#field" y si no, por name="field".
 */
function markInvalidFields(form: HTMLFormElement, errors: unknown) {
  // Limpia estado previo
  form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));

  // Solo aplicable a object {field: reason}
  if (!errors || typeof errors !== 'object' || Array.isArray(errors)) return;

  const rec = errors as Record<string, unknown>;
  for (const field of Object.keys(rec)) {
    // id o name
    const selector = `#${CSS.escape(field)}, [name="${CSS.escape(field)}"]`;
    const el = form.querySelector<HTMLElement>(selector);
    if (el) el.classList.add('is-invalid');
  }
}

export async function transmissioDadesDB(
  event: Event,
  tipus: string,
  formId: string,
  urlAjax: string,
  neteja?: boolean, // mantiene compatibilidad
  successBehavior: SuccessBehavior = 'none' // 'none' | 'hide' | 'disable'
): Promise<void> {
  event.preventDefault();

  const form = document.getElementById(formId) as HTMLFormElement;
  if (!form) {
    console.error(`Form with id ${formId} not found`);
    return;
  }

  // Crear un objeto para almacenar los datos del formulario
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

  const jsonData = JSON.stringify(formData);

  try {
    const response = await fetch(urlAjax, {
      method: tipus,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: jsonData,
    });

    const data = await response.json();

    const okMessageDiv = document.getElementById('okMessage');
    const okTextDiv = document.getElementById('okText');
    const errMessageDiv = document.getElementById('errMessage');
    const errTextDiv = document.getElementById('errText');

    if (!okMessageDiv || !okTextDiv || !errMessageDiv || !errTextDiv) return;

    // Si todo OK a nivel HTTP
    if (response.ok) {
      // === SUCCESS de negocio ===
      if (data.status === 'success') {
        // Limpia invalids si veníamos de un error previo
        markInvalidFields(form, null);

        missatgesBackend({
          tipus: 'success',
          missatge: data.message || Missatges.success.default,
          contenidor: okMessageDiv,
          text: okTextDiv,
          altreContenidor: errMessageDiv,
        });

        // === Comportamiento genérico en éxito ===
        const method = tipus.toUpperCase();
        const shouldReset = neteja ?? method === 'POST'; // si no pasas 'neteja', por defecto resetea en POST

        if (successBehavior === 'hide') {
          form.hidden = true;
          history.replaceState({}, document.title, window.location.pathname);
        } else if (successBehavior === 'disable') {
          form.querySelectorAll<HTMLElement>('input,select,textarea,button,[contenteditable],trix-editor').forEach((el) => el.setAttribute('disabled', 'true'));
          history.replaceState({}, document.title, window.location.pathname);
        } else if (shouldReset) {
          resetForm(formId);
        }

        // === CTA genérico "ver ficha" usando plantilla del form ===
        const template = (form.dataset as any).successRedirectTemplate as string | undefined;
        if (template && typeof data?.slug === 'string' && data.slug.length > 0) {
          const href = template.replace('{slug}', encodeURIComponent(data.slug));
          const actions = document.getElementById('successActions') || okMessageDiv;

          // evita duplicados
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

        // Evento genérico
        const ev = new CustomEvent('form:success', { detail: data });
        form.dispatchEvent(ev);
      } else {
        // === ERROR de negocio con HTTP 200 ===
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

        // Marca campos inválidos si procede
        markInvalidFields(form, data.errors);
      }
    } else {
      // === ERROR HTTP (400/500 etc) ===
      const missatge = `
        ${data.message ? `<p>${data.message}</p>` : ''}
        ${renderErrors(data.errors)}
      `;

      missatgesBackend({
        tipus: 'error',
        missatge,
        contenidor: errMessageDiv,
        text: errTextDiv,
        altreContenidor: okMessageDiv,
      });

      // Marca campos inválidos si procede
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
