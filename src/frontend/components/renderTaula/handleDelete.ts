let deleteListenerAdded = false;
const reloadCallbacks: Record<string, () => void> = {};

export function registerDeleteCallback(key: string, callback: () => void) {
  reloadCallbacks[key] = callback;
}

export function initDeleteHandlers() {
  if (deleteListenerAdded) return;
  deleteListenerAdded = true;

  document.addEventListener('click', async (event: Event) => {
    const target = event.target as HTMLElement;
    const button = target.closest('.delete-button') as HTMLElement | null;
    if (!button) return;

    event.preventDefault();

    const id = button.dataset.id;
    const url = button.dataset.url;
    const reloadKey = button.dataset.reloadCallback;

    if (!id || !url) return;

    const confirmed = confirm('Segur que vols eliminar aquest registre?');
    if (!confirmed) return;

    try {
      const response = await fetch(url, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
        },
      });

      const data = await response.json();

      if (response.ok && data.status === 'success') {
        alert('Registre eliminat correctament.');

        if (reloadKey && reloadCallbacks[reloadKey]) {
          reloadCallbacks[reloadKey]();
        } else {
          const rowElement = button.closest('tr');
          if (rowElement) rowElement.remove();
        }
      } else {
        alert(data.message || 'Error en eliminar el registre.');
      }
    } catch (error) {
      console.error('Error al eliminar:', error);
      alert('Error de xarxa en eliminar el registre.');
    }
  });
}
