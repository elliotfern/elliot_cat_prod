import { setWithExpiry, getWithExpiry } from '../localStorage/localStorage';

// Función para obtener el estado de admin, usando localStorage para evitar llamadas repetidas
export async function getIsAdmin() {
  const isAdmin = getWithExpiry('isAdmin');
  if (isAdmin !== null) return isAdmin;

  const isAdminFromApi = await isAdminUser();
  setWithExpiry('isAdmin', isAdminFromApi, 1800); // 30 minutos de duración
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
