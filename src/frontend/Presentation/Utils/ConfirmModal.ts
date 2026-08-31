import { Modal } from 'bootstrap';

export function showConfirmModal(message: string, confirmText: string = 'Confirmar', cancelText: string = 'Cancel·lar'): Promise<boolean> {
  return new Promise((resolve) => {
    const existingModal = document.getElementById('confirmModal');

    if (existingModal) {
      existingModal.remove();
    }

    const modal = document.createElement('div');

    modal.id = 'confirmModal';
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    modal.setAttribute('aria-hidden', 'true');

    modal.innerHTML = `
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title">Confirmació</h5>
            <button
              type="button"
              class="btn-close"
              data-confirm-cancel
              aria-label="Tancar"
            ></button>
          </div>

          <div class="modal-body">
            <p class="mb-0">${message}</p>
          </div>

          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-confirm-cancel
            >
              ${cancelText}
            </button>

            <button
              type="button"
              class="btn btn-danger"
              data-confirm-ok
            >
              ${confirmText}
            </button>
          </div>

        </div>
      </div>
    `;

    document.body.appendChild(modal);

    // Bootstrap 5
    const bootstrapModal = new Modal(modal);

    let resolved = false;

    const finish = (result: boolean) => {
      if (resolved) return;

      resolved = true;

      modal.addEventListener(
        'hidden.bs.modal',
        () => {
          modal.remove();
          resolve(result);
        },
        { once: true }
      );

      bootstrapModal.hide();
    };

    modal.querySelectorAll<HTMLElement>('[data-confirm-cancel]').forEach((button) => {
      button.addEventListener('click', () => {
        finish(false);
      });
    });

    modal.querySelector('[data-confirm-ok]')?.addEventListener('click', () => {
      finish(true);
    });

    modal.addEventListener(
      'hidden.bs.modal',
      () => {
        if (!resolved) {
          resolved = true;
          modal.remove();
          resolve(false);
        }
      },
      { once: true }
    );

    bootstrapModal.show();
  });
}
