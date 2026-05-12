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
    const response = await fetch('https://api.elliot.cat/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify({ email, password }),
    });

    const data = await response.json();

    // ❌ ERROR
    if (!response.ok || data.success !== true) {
      const msg = data.message || "Error d'autenticació";

      const extra = Array.isArray(data.errors) && data.errors.length ? `<br><small>${data.errors.join('<br>')}</small>` : '';

      showError(msg + extra);
      return;
    }

    // ✅ SUCCESS
    showSuccess(data.message || 'Accés permès');

    setTimeout(() => {
      const redirect = data.user_type === 1 ? '/gestio/admin' : '/usuaris';

      window.location.href = redirect;
    }, 800);
  } catch (error) {
    showError('Error de connexió amb el servidor');
  }
}
