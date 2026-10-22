import { renderLoginForm } from '../../services/login/renderLoginForm';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_BASE } from '../../utils/urls';

export function formRegistreUsuari(): void {
  const form = document.getElementById('formUsuari');
  const passwordInput = document.getElementById('password');
  const passwordConfirmInput = document.getElementById('passwordConfirm');
  const passwordConfirmError = document.getElementById('passwordConfirmError');
  const privacitatInput = document.querySelector<HTMLInputElement>('[name="acceptaPrivacitat"]');

  if (!(form instanceof HTMLFormElement) || !(passwordInput instanceof HTMLInputElement) || !(passwordConfirmInput instanceof HTMLInputElement) || !(passwordConfirmError instanceof HTMLDivElement) || !(privacitatInput instanceof HTMLInputElement)) {
    console.error('No s’han trobat tots els elements del formulari de registre.');
    return;
  }

  const passwordElement = passwordInput;
  const passwordConfirmElement = passwordConfirmInput;
  const passwordConfirmErrorElement = passwordConfirmError;

  // Mostrar o ocultar les contrasenyes.
  const toggleButtons = form.querySelectorAll<HTMLButtonElement>('[data-toggle-password]');

  toggleButtons.forEach((button) => {
    button.addEventListener('click', function () {
      const inputId = button.dataset.togglePassword;
      const input = inputId ? document.getElementById(inputId) : null;

      if (!(input instanceof HTMLInputElement)) return;

      const mostrar = input.type === 'password';

      input.type = mostrar ? 'text' : 'password';
      button.textContent = mostrar ? '◎' : '◉';
      button.setAttribute('aria-label', mostrar ? 'Amagar contrasenya' : 'Mostrar contrasenya');
      button.setAttribute('aria-pressed', String(mostrar));
    });
  });

  // Validar que les dues contrasenyes coincideixin.
  function validarConfirmacio(): boolean {
    const hiHaConfirmacio = passwordConfirmElement.value.length > 0;
    const coincideixen = passwordElement.value === passwordConfirmElement.value;
    const mostrarError = hiHaConfirmacio && !coincideixen;

    passwordConfirmElement.classList.toggle('is-invalid', mostrarError);
    passwordConfirmElement.classList.toggle('is-valid', hiHaConfirmacio && coincideixen);

    passwordConfirmErrorElement.textContent = mostrarError ? 'Les contrasenyes no coincideixen.' : '';

    passwordConfirmErrorElement.classList.toggle('d-block', mostrarError);

    return hiHaConfirmacio && coincideixen;
  }

  passwordInput.addEventListener('input', () => {
    if (passwordConfirmInput.value.length > 0) {
      validarConfirmacio();
    }
  });

  passwordConfirmInput.addEventListener('input', validarConfirmacio);

  form.addEventListener('form:success', () => {
    const emailInput = document.getElementById('email');

    const email = emailInput instanceof HTMLInputElement ? emailInput.value : '';

    renderLoginForm(form, email);
  });

  // Enviar el formulari.
  form.addEventListener('submit', function (event) {
    if (passwordInput.value.length < 12) {
      event.preventDefault();
      passwordInput.focus();
      return;
    }

    if (!validarConfirmacio()) {
      event.preventDefault();
      passwordConfirmInput.focus();
      return;
    }

    if (!privacitatInput.checked) {
      event.preventDefault();
      privacitatInput.focus();
      return;
    }

    void transmissioDadesDB(event, 'POST', 'formUsuari', `${API_BASE}/usuaris/post/registre`, false);
  });
}
