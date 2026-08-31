// src/Presentation/Utils/formMessages.ts

import { markInvalidFields } from './formErrors';
import { missatgesBackend } from './missatgesBackend';

export function showSuccess(form: HTMLFormElement, message: string): void {
  // Limpiar errores anteriores
  markInvalidFields(form, null);

  const okMessageDiv = document.getElementById('okMessage');
  const okTextDiv = document.getElementById('okText');
  const errMessageDiv = document.getElementById('errMessage');

  if (!okMessageDiv || !okTextDiv) {
    return;
  }

  missatgesBackend({
    tipus: 'success',
    missatge: message,
    contenidor: okMessageDiv,
    text: okTextDiv,
    altreContenidor: errMessageDiv ?? undefined,
  });
}

export function showError(form: HTMLFormElement, error: any, defaultMessage: string): void {
  const errMessageDiv = document.getElementById('errMessage');
  const errTextDiv = document.getElementById('errText');
  const okMessageDiv = document.getElementById('okMessage');

  if (errMessageDiv && errTextDiv) {
    missatgesBackend({
      tipus: 'error',
      missatge: error?.message || defaultMessage,
      contenidor: errMessageDiv,
      text: errTextDiv,
      altreContenidor: okMessageDiv ?? undefined,
    });
  }

  // Marcar errores de los campos
  markInvalidFields(form, error?.errors);
}
