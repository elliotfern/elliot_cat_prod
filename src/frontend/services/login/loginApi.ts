import { authenticateUser } from './loginService';

export async function loginApi(event: Event): Promise<void> {
  event.preventDefault();

  const emailInput = document.getElementById('email');
  const passwordInput = document.getElementById('password');
  const okBox = document.getElementById('okMessage');
  const errBox = document.getElementById('errMessage');

  if (!(emailInput instanceof HTMLInputElement) || !(passwordInput instanceof HTMLInputElement) || !okBox || !errBox) {
    return;
  }

  const email = emailInput.value;
  const password = passwordInput.value;

  const showError = (message: string): void => {
    errBox.className = 'alert alert-danger';
    errBox.classList.remove('d-none');
    errBox.textContent = message;

    okBox.classList.add('d-none');
  };

  const showSuccess = (message: string): void => {
    okBox.className = 'alert alert-success';
    okBox.classList.remove('d-none');
    okBox.textContent = message;

    errBox.classList.add('d-none');
  };

  try {
    const response = await authenticateUser(email, password);

    showSuccess(response.message || 'Accés permès');

    window.setTimeout(() => {
      const redirect = response.data.user.role === 'admin' ? '/gestio/admin' : '/usuaris';

      window.location.href = redirect;
    }, 800);
  } catch (error: unknown) {
    console.error('Error durant l’autenticació:', error);

    showError(error instanceof Error ? error.message : 'Error de connexió amb el servidor');
  }
}
