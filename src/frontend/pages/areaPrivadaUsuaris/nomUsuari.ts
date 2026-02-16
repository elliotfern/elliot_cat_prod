export async function nomUsuari(): Promise<void> {
  const urlAjax = `https://${window.location.hostname}/api/auth/get/nomUsuari`;

  const userDiv = document.getElementById('benvingudaUsuari');
  if (userDiv) {
    try {
      const res = await fetch(urlAjax, {
        credentials: 'include', // importante si la cookie "token" es HttpOnly
      });

      if (!res.ok) {
        console.warn('No se pudo obtener el usuario');
        return;
      }

      const data = await res.json();

      const welcomeMessage = data.nom ? `Hola, ${data.nom}` : 'Usuari desconegut';
      userDiv.textContent = welcomeMessage;
    } catch (err) {
      console.error('Error al obtener el usuario:', err);
    }
  } else {
    console.warn('Elemento #userDiv no encontrado');
  }
}
