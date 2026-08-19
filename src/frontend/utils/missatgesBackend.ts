export function missatgesBackend({ tipus, missatge, contenidor, text, altreContenidor }: { tipus: 'success' | 'error'; missatge: string; contenidor: HTMLElement; text: HTMLElement; altreContenidor?: HTMLElement }): void {
  // Ocultar el otro mensaje
  if (altreContenidor) {
    altreContenidor.classList.add('d-none');
    altreContenidor.classList.remove('alert-success', 'alert-danger');
  }

  const heading = tipus === 'success' ? 'Transmissió de dades correcta!' : 'Error en les dades!';

  text.innerHTML = `
    <h4 class="alert-heading"><strong>${heading}</strong></h4>
    <div class="mt-2">${missatge}</div>
  `;

  // Mostrar el mensaje actual
  contenidor.classList.remove('d-none');
  contenidor.classList.remove('alert-success', 'alert-danger');
  contenidor.classList.add(tipus === 'success' ? 'alert-success' : 'alert-danger');

  contenidor.scrollIntoView({
    behavior: 'smooth',
    block: 'center',
  });

  // Ocultar después de 15 segundos
  setTimeout(() => {
    contenidor.classList.add('d-none');
    contenidor.classList.remove('alert-success', 'alert-danger');
  }, 15000);
}
