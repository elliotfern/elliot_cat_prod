import { showConfirmModal } from '../../Utils/ConfirmModal';
import { showFlashMessage } from '../../Utils/FlashMessage';

type DeleteHandler = (id: string) => Promise<void>;

let deleteListenerAdded = false;

const deleteHandlers: Record<string, DeleteHandler> = {};
const reloadCallbacks: Record<string, () => void> = {};

export function registerDeleteHandler(key: string, handler: DeleteHandler): void {
  deleteHandlers[key] = handler;
}

export function registerDeleteCallback(key: string, callback: () => void): void {
  reloadCallbacks[key] = callback;
}

export function initDeleteHandlers(): void {
  if (deleteListenerAdded) return;

  deleteListenerAdded = true;

  document.addEventListener('click', async (event: Event) => {
    const target = event.target as HTMLElement;

    const button = target.closest('.delete-button') as HTMLElement | null;

    if (!button) return;

    event.preventDefault();

    const id = button.dataset.id;
    const deleteHandler = button.dataset.deleteHandler;
    const reloadKey = button.dataset.reloadCallback;

    if (!id || !deleteHandler) return;

    const handler = deleteHandlers[deleteHandler];

    if (!handler) {
      showFlashMessage(`No existeix un handler d'eliminació per a "${deleteHandler}".`, 'danger');
      return;
    }

    const confirmed = await showConfirmModal('Segur que vols eliminar aquest registre?', 'Elimina', 'Cancel·lar');

    if (!confirmed) return;

    try {
      await handler(id);

      showFlashMessage('Registre eliminat correctament.', 'success');

      if (reloadKey && reloadCallbacks[reloadKey]) {
        reloadCallbacks[reloadKey]();
      } else {
        const rowElement = button.closest('tr');

        if (rowElement) {
          rowElement.remove();
        }
      }
    } catch (error: any) {
      console.error('Error al eliminar:', error);

      showFlashMessage(error?.message || 'Error en eliminar el registre.', 'danger');
    }
  });
}
