export function showFlashMessage(message: string, type: 'success' | 'danger' | 'warning' | 'info' = 'success', duration = 4000): void {
  let container = document.getElementById('flashMessages');

  if (!container) {
    container = document.createElement('div');
    container.id = 'flashMessages';

    container.className = `
      position-fixed
      top-0
      end-0
      p-3
    `;

    container.style.zIndex = '1080';

    document.body.appendChild(container);
  }

  const alert = document.createElement('div');

  alert.className = `alert alert-${type} alert-dismissible fade show shadow`;
  alert.setAttribute('role', 'alert');

  alert.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  `;

  container.appendChild(alert);

  window.setTimeout(() => {
    alert.classList.remove('show');

    window.setTimeout(() => {
      alert.remove();
    }, 150);
  }, duration);
}
