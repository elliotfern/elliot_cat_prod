export async function loginApi(event: Event) {
  event.preventDefault();

  const emailInput = document.getElementById('email') as HTMLInputElement;
  const passwordInput = document.getElementById('password') as HTMLInputElement;

  const okBox = document.getElementById('okMessage');
  const errBox = document.getElementById('errMessage');

  if (!emailInput || !passwordInput) return;

  const email = emailInput.value;
  const password = passwordInput.value;

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

    const showError = (msg: string) => {
      if (!errBox || !okBox) return;
      errBox.classList.remove('d-none');
      errBox.innerHTML = msg;
      okBox.classList.add('d-none');
    };

    const showSuccess = (msg: string) => {
      if (!errBox || !okBox) return;
      okBox.classList.remove('d-none');
      okBox.innerHTML = msg;
      errBox.classList.add('d-none');
    };

    // ❌ ERROR
    if (!response.ok || data.status !== 'success') {
      const apiMsg = data.message || 'Error d’autenticació';

      const extraErrors = Array.isArray(data.errors) && data.errors.length ? `<br><small>${data.errors.join('<br>')}</small>` : '';

      showError(apiMsg + extraErrors);
      return;
    }

    // ✅ OK
    showSuccess(data.message || 'Accés permès');

    setTimeout(() => {
      window.location.href = data.user_type === 1 ? '/gestio/admin' : '/usuaris';
    }, 1500);
  } catch (error) {
    if (errBox && okBox) {
      errBox.classList.remove('d-none');
      errBox.innerHTML = 'Error de connexió amb el servidor';
      okBox.classList.add('d-none');
    }
  }
}
