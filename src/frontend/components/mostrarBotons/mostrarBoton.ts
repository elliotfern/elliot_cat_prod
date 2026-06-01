import { getIsAdmin } from '../../services/auth/isAdmin';

export async function mostrarBotonsNomesAdmin() {
  const isAdmin = await getIsAdmin();

  const buttonContainer = document.getElementById('isAdminButton');

  if (isAdmin) {
    // Si es admin, mostramos el botón
    if (buttonContainer) {
      buttonContainer.style.display = 'block';
    }
  } else {
    // Si no es admin, eliminamos el botón del DOM
    if (buttonContainer) {
      buttonContainer.remove(); // Elimina el botón del DOM
    }
  }
}
