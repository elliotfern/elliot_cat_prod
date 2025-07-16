import { getIsAdmin } from '../auth/isAdmin';

function handleLogin() {
  const isAdmin = localStorage.getItem('isAdmin');
  if (isAdmin === 'false') {
    localStorage.removeItem('isAdmin');
  }
}

export async function loginApi(event: Event) {
  event.preventDefault();

  const emailInput = document.getElementById('email') as HTMLInputElement;
  const passwordInput = document.getElementById('password') as HTMLInputElement;

  const loginMessageOk = document.getElementById('loginMessageOk');
  const loginMessageErr = document.getElementById('loginMessageErr');

  if (emailInput && passwordInput) {
    const email = emailInput.value;
    const password = passwordInput.value;

    try {
      const response = await fetch('https://api.elliot.cat/api/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'include', // 👈 Necesario para que la cookie JWT se guarde
        body: JSON.stringify({ email, password }), // ✅ Campo correcto
      });

      const data = await response.json();

      if (response.ok && data.success) {
        handleLogin();

        // Aquí podrías guardar datos si los necesitas
        if (data.user_type === 1) {
          localStorage.setItem('isAdmin', 'true');
        } else {
          localStorage.setItem('isAdmin', 'false');
        }

        if (loginMessageOk && loginMessageErr) {
          loginMessageOk.style.display = 'block';
          loginMessageOk.innerHTML = data.message;
          loginMessageErr.style.display = 'none';
        }

        setTimeout(() => {
          window.location.href = data.user_type === 1 ? '/gestio/admin' : '/usuaris';
        }, 2000);
      } else {
        if (loginMessageOk && loginMessageErr) {
          loginMessageErr.style.display = 'block';
          loginMessageErr.innerHTML = data.message || "Error d'autenticació";
          loginMessageOk.style.display = 'none';
        }
      }
    } catch (error) {
      if (loginMessageErr && loginMessageOk) {
        loginMessageErr.style.display = 'block';
        loginMessageErr.innerHTML = 'Error del servidor';
        loginMessageOk.style.display = 'none';
      }
    }
  }
}
