import { loginApi } from '../../services/login/loginApi';

export function loginPage() {
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Prevenir el comportament per defecte del formulari
        loginApi(event);
    });
  }
}
