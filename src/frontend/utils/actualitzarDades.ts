import { Missatges } from './locales/missatges';
import { missatgesBackend } from './missatgesBackend';
import { resetForm } from './resetForm';

// Comportamiento genérico en éxito
type SuccessBehavior = 'none' | 'hide' | 'disable';

export async function transmissioDadesDB(
  event: Event,
  tipus: string,
  formId: string,
  urlAjax: string,
  neteja?: boolean, // mantiene compatibilidad
  successBehavior: SuccessBehavior = 'none' // NUEVO: 'none' | 'hide' | 'disable'
): Promise<void> {
  event.preventDefault();

  const form = document.getElementById(formId) as HTMLFormElement;
  if (!form) {
    console.error(`Form with id ${formId} not found`);
    return;
  }

  // Crear un objeto para almacenar los datos del formulario
  const formDataRaw = new FormData(form);
  const formData: { [key: string]: FormDataEntryValue } = {};

  formDataRaw.forEach((value, key) => {
    formData[key] = value;
  });

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
    if (response.ok) {
      if (data.status === 'success') {
        missatgesBackend({
          tipus: 'success',
          missatge: data.message || Missatges.success.default,
          contenidor: okMessageDiv,
          text: okTextDiv,
          altreContenidor: errMessageDiv,
        });

        // === NUEVO: comportamiento genérico en éxito ===
        const method = tipus.toUpperCase();
        const shouldReset = neteja ?? method === 'POST'; // si no pasas 'neteja', por defecto resetea en POST

        if (successBehavior === 'hide') {
          form.hidden = true;
          history.replaceState({}, document.title, window.location.pathname);
        } else if (successBehavior === 'disable') {
          form.querySelectorAll<HTMLElement>('input,select,textarea,button,[contenteditable],trix-editor').forEach((el) => el.setAttribute('disabled', 'true'));
          history.replaceState({}, document.title, window.location.pathname);
        } else if (shouldReset) {
          resetForm(formId); // ← asegura que tu resetForm vacía también los trix y multi-selects
        }

        // === NUEVO: CTA genérico "ver ficha" usando plantilla del form ===
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

        // Dispara un evento genérico para que cada página haga lo suyo (enlaces, navegación, etc.)
        const ev = new CustomEvent('form:success', { detail: data });
        form.dispatchEvent(ev);
      } else {
        const missatge = `
          ${data.message ? `<p>${data.message}</p>` : ''}
          ${data.errors && data.errors.length > 0 ? `<ul>${data.errors.map((e: string) => `<li>${e}</li>`).join('')}</ul>` : `<p>${Missatges.error.default}</p>`}
        `;

        missatgesBackend({
          tipus: 'error',
          missatge,
          contenidor: errMessageDiv,
          text: errTextDiv,
          altreContenidor: okMessageDiv,
        });
      }
    } else {
      const missatge = `
          ${data.message ? `<p>${data.message}</p>` : ''}
          ${data.errors && data.errors.length > 0 ? `<ul>${data.errors.map((e: string) => `<li>${e}</li>`).join('')}</ul>` : ``}
        `;

      missatgesBackend({
        tipus: 'error',
        missatge,
        contenidor: errMessageDiv,
        text: errTextDiv,
        altreContenidor: okMessageDiv,
      });
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
