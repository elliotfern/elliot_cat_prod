type MessageType = 'success' | 'error';

function showMessageDelete(type: MessageType, message: string): void {
  const existing = document.getElementById('globalMessage');

  if (existing) {
    existing.remove();
  }

  const container = document.createElement('div');

  container.id = 'globalMessage';

  container.className = `
    alert
    ${type === 'success' ? 'alert-success' : 'alert-danger'}
    alert-dismissible
    fade
    show
    position-fixed
    top-0
    start-50
    translate-middle-x
    mt-3
    shadow
  `;

  container.style.zIndex = '1080';
  container.setAttribute('role', 'alert');

  const heading = type === 'success' ? 'Operació correcta!' : 'Error!';

  container.innerHTML = `
    <div class="fw-semibold">${heading}</div>
    <div class="mt-1">${message}</div>

    <button
      type="button"
      class="btn-close"
      aria-label="Tanca"
    ></button>
  `;

  document.body.appendChild(container);

  const closeButton = container.querySelector('.btn-close');

  closeButton?.addEventListener('click', () => {
    container.remove();
  });

  setTimeout(() => {
    container.remove();
  }, 15000);
}

export function showSuccessDelete(message: string): void {
  showMessageDelete('success', message);
}

export function showErrorDelete(message: string): void {
  showMessageDelete('error', message);
}
