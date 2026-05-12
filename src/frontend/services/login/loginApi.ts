export async function loginApi(event: Event) {
  event.preventDefault();

  const emailInput = document.getElementById('email') as HTMLInputElement;
  const passwordInput = document.getElementById('password') as HTMLInputElement;

  const okBox = document.getElementById('okMessage');
  const errBox = document.getElementById('errMessage');

  if (!emailInput || !passwordInput || !okBox || !errBox) return;

  const email = emailInput.value;
  const password = passwordInput.value;

  const showError = (msg: string) => {
    errBox.className = 'alert alert-danger';
    errBox.classList.remove('d-none');

    okBox.classList.add('d-none');

    errBox.innerHTML = msg;
  };

  const showSuccess = (msg: string) => {
    okBox.className = 'alert alert-success';
    okBox.classList.remove('d-none');

    errBox.classList.add('d-none');

    okBox.innerHTML = msg;
  };

  try {
    // 1️⃣ LOGIN
    const response = await fetch('https://api.elliot.cat/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify({ email, password }),
    });

    const data = await response.json();

    if (!response.ok || data.status !== 'success') {
      const apiMsg = data.message || "Error d'autenticació";

      const extraErrors = Array.isArray(data.errors) && data.errors.length ? `<br><small>${data.errors.join('<br>')}</small>` : '';

      showError(apiMsg + extraErrors);
      return;
    }

    // 2️⃣ LOGIN OK
    showSuccess(data.message || 'Accés permès');

    // 3️⃣ CONSULTAR USUARI DESDE COOKIE
    setTimeout(async () => {
      try {
        const meResponse = await fetch('https://elliot.cat/api/auth/get/?me', {
          method: 'GET',
          credentials: 'include',
        });

        const me = await meResponse.json();

        if (!me.authenticated) {
          window.location.href = '/usuaris';
          return;
        }

        // 4️⃣ REDIRECCIÓN SEGÚN ROL
        const redirectUrl = me.user_type === 1 ? '/gestio/admin' : '/usuaris';

        window.location.href = redirectUrl;
      } catch (e) {
        // fallback seguro
        window.location.href = '/usuaris';
      }
    }, 800);
  } catch (error) {
    showError('Error de connexió amb el servidor');
  }
}
