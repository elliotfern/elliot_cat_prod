export async function getIsAdmin() {
  const item = localStorage.getItem('isAdmin');

  if (item) {
    try {
      const parsed = JSON.parse(item);
      const now = Date.now();

      if (parsed.expiry > now) {
        return parsed.value; // válido
      } else {
        localStorage.removeItem('isAdmin'); // expirado
      }
    } catch (e) {
      console.error('Valor de isAdmin corrupto:', e);
      localStorage.removeItem('isAdmin');
    }
  }

  // Si no hay valor o está expirado, pedir a la API
  const isAdminFromApi = await isAdminUser();
  localStorage.setItem(
    'isAdmin',
    JSON.stringify({
      value: isAdminFromApi,
      expiry: Date.now() + 30 * 60 * 1000, // 30 minutos
    })
  );
  return isAdminFromApi;
}

export async function isAdminUser(): Promise<boolean> {
  try {
    // Cridem a l'endpoint que verifica si l'usuari és admin
    const url = `https://api.elliot.cat/api/auth/get`;
    const response = await fetch(url, {
      credentials: 'include', // Necessari per enviar les cookies amb la petició
    });

    if (!response.ok) {
      throw new Error('No es pot verificar si és admin');
    }

    const data = await response.json();
    return data.isAdmin;
  } catch (error) {
    console.error('Error al verificar admin:', error);
    return false;
  }
}
