import { loginApi } from './loginApi';

export function renderLoginForm(form: HTMLFormElement, email: string): void {
  const container = form.parentElement;

  if (!container) return;

  // Actualizar el título de la página.
  const heading = container.querySelector('h1');

  if (heading) {
    heading.textContent = 'Inicia sessió';
  }

  // Substituir només el formulari de registre.
  // Els missatges d'èxit i error es conserven.
  const emailSegur = email.replace(/[&"<>]/g, (caracter) => {
    const entitats: Record<string, string> = {
      '&': '&amp;',
      '"': '&quot;',
      '<': '&lt;',
      '>': '&gt;',
    };

    return entitats[caracter] ?? caracter;
  });

  form.outerHTML = `
    <form id="formLogin" method="post">
      <div class="mb-3">
        <label for="email" class="form-label">Correu electrònic</label>
        <input
          type="email"
          class="form-control"
          id="email"
          name="email"
          value="${emailSegur}"
          autocomplete="username"
          required
        >
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Contrasenya</label>
        <input
          type="password"
          class="form-control"
          id="password"
          name="password"
          autocomplete="current-password"
          required
        >
      </div>

      <button type="submit" class="btn btn-primary">
        Iniciar sessió
      </button>
    </form>
  `;

  const loginForm = container.querySelector('#formLogin');

  if (loginForm instanceof HTMLFormElement) {
    loginForm.addEventListener('submit', (event) => {
      void loginApi(event);
    });
  }
}
