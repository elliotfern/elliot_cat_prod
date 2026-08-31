import { api } from '../../Infrastructure/Api/Client/ApiClient';

export async function logout() {
  try {
    const data = await api.get<any>('auth/get/?logOut');

    if (data.message === 'OK') {
      localStorage.clear();
      sessionStorage.clear();

      window.location.href = 'https://elliot.cat';
    } else {
      console.error('Error al hacer logout:', data);
    }
  } catch (error) {
    console.error('Error en logout:', error);
  }
}
