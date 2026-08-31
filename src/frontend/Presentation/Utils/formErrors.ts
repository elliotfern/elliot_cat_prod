type FieldError = {
  label?: string;
  messages?: string[] | string;
};

/**
 * Normaliza los errores recibidos del backend
 * a un array de mensajes.
 */
export function normalizeFieldErrors(error: FieldError): string[] {
  if (!error) return [];

  if (Array.isArray(error.messages)) {
    return error.messages;
  }

  if (typeof error.messages === 'string') {
    return [error.messages];
  }

  return [];
}

/**
 * Muestra un error asociado a un SELECT
 * utilizando el componente Choices.js.
 */
export function setChoicesError(select: HTMLSelectElement, message: string): void {
  const wrapper = select.closest('.choices');

  if (!wrapper) return;

  // Limpiar error anterior
  const old = wrapper.parentElement?.querySelector('.choices-error');

  if (old) {
    old.remove();
  }

  // Marcar visualmente
  wrapper.classList.add('is-invalid');

  // Crear mensaje inline
  const errorDiv = document.createElement('div');

  errorDiv.className = 'choices-error text-danger small mt-1';

  errorDiv.innerHTML = message;

  // Insertar debajo del componente
  wrapper.parentElement?.appendChild(errorDiv);
}

/**
 * Elimina todos los errores visuales asociados
 * a componentes Choices.js.
 */
export function clearChoicesErrors(form: HTMLFormElement): void {
  form.querySelectorAll('.choices.is-invalid').forEach((el) => {
    el.classList.remove('is-invalid');
  });

  form.querySelectorAll('.choices-error').forEach((el) => {
    el.remove();
  });
}

/**
 * Marca visualmente los campos que contienen
 * errores devueltos por el backend.
 */
export function markInvalidFields(form: HTMLFormElement, errors: unknown): void {
  // Reset del estado visual anterior
  form.querySelectorAll('.is-invalid').forEach((el) => {
    el.classList.remove('is-invalid');
  });

  form.querySelectorAll('.invalid-feedback').forEach((el) => {
    el.innerHTML = '';
  });

  clearChoicesErrors(form);

  if (!errors || typeof errors !== 'object') {
    return;
  }

  for (const [field, errorRaw] of Object.entries(errors)) {
    const error = errorRaw as FieldError;

    const element = form.querySelector(`[name="${field}"]`);

    if (!element) {
      continue;
    }

    const label = error.label ?? field;

    const messages = normalizeFieldErrors(error);

    const htmlMessages = messages.filter(Boolean).join('<br>');

    if (!htmlMessages) {
      continue;
    }

    // SELECT + Choices.js
    if (element.tagName === 'SELECT') {
      setChoicesError(
        element as HTMLSelectElement,
        `
          <div class="fw-semibold">${label}</div>
          <div>${htmlMessages}</div>
        `
      );

      continue;
    }

    // Inputs, textareas, etc.
    element.classList.add('is-invalid');

    const errorBox = document.getElementById(`error-${field}`);

    if (errorBox) {
      errorBox.innerHTML = `
        <div class="fw-semibold">${label}</div>
        <div>${htmlMessages}</div>
      `;
    }
  }
}
