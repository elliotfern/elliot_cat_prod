export function missatgesBackend({ tipus, missatge, contenidor, text, altreContenidor }: { tipus: 'success' | 'error'; missatge: string; contenidor: HTMLElement; text: HTMLElement; altreContenidor?: HTMLElement }): void {
  if (altreContenidor) {
    altreContenidor.style.display = 'none';
    altreContenidor.classList.remove('alert-success', 'alert-danger');
  }

  const heading = tipus === 'success' ? 'Transmissió de dades correcta!' : 'Error en les dades!';

  text.innerHTML = `
    <h4 class="alert-heading"><strong>${heading}</strong></h4>
    <div class="mt-2">${missatge}</div>
  `;

  contenidor.style.display = 'block';
  contenidor.classList.remove('alert-success', 'alert-danger');
  contenidor.classList.add(tipus === 'success' ? 'alert-success' : 'alert-danger');

  contenidor.scrollIntoView({ behavior: 'smooth', block: 'center' });

  setTimeout(() => {
    contenidor.style.display = 'none';
    contenidor.classList.remove('alert-success', 'alert-danger');
  }, 15000);
}
