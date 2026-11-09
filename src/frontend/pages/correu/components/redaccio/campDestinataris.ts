import { cercarContactes } from '../../services/correuApi';
import type { Contacte, ContacteCorreu } from '../../types/Correu';
import { crearElement } from '../../utils/dom';

interface CampDestinataris {
  contenidor: HTMLElement;
  obtenirDestinataris: () => ContacteCorreu[];
  netejar: () => void;
  validar: () => boolean;
}

const TEMPS_ESPERA_CERCA = 300;

function obtenirNom(contacte: Contacte): string {
  const nomComplet = [contacte.nom, contacte.cognoms]
    .filter((part): part is string => typeof part === 'string' && part.trim() !== '')
    .join(' ')
    .trim();

  return nomComplet || contacte.empresa?.trim() || contacte.email || '';
}

function esEmailValid(email: string): boolean {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

export function crearCampDestinataris(id: string, etiqueta: string, obligatori = false, classes = 'col-12 col-md-6'): CampDestinataris {
  const contenidor = crearElement('div', classes);

  const label = crearElement('label', 'form-label', etiqueta);
  label.htmlFor = id;

  const camp = crearElement('div', 'form-control d-flex flex-wrap align-items-center gap-2');

  const destinataris: ContacteCorreu[] = [];

  const input = crearElement('input', 'border-0 p-0 flex-grow-1');
  input.type = 'text';
  input.id = id;
  input.placeholder = 'Nom o correu electrònic...';
  input.autocomplete = 'off';
  input.style.minWidth = '180px';
  input.setAttribute('role', 'combobox');
  input.setAttribute('aria-autocomplete', 'list');
  input.setAttribute('aria-expanded', 'false');

  const suggeriments = crearElement('div', 'list-group position-absolute top-100 start-0 w-100 shadow');

  suggeriments.id = `${id}-suggeriments`;
  suggeriments.setAttribute('role', 'listbox');
  suggeriments.hidden = true;

  input.setAttribute('aria-controls', suggeriments.id);

  const zonaSuggeriments = crearElement('div', 'position-relative');

  const estat = crearElement('div', 'small text-body-secondary mt-1');
  estat.setAttribute('role', 'status');
  estat.setAttribute('aria-live', 'polite');

  zonaSuggeriments.appendChild(suggeriments);
  camp.append(input);
  contenidor.append(label, camp, zonaSuggeriments, estat);

  let resultats: Contacte[] = [];
  let indexActiu = -1;
  let temporitzador: ReturnType<typeof setTimeout> | undefined;
  let identificadorCerca = 0;

  function mostrarSuggeriments(mostrar: boolean): void {
    suggeriments.hidden = !mostrar;
    input.setAttribute('aria-expanded', String(mostrar));
  }

  function actualitzarValidacio(): void {
    input.setCustomValidity(obligatori && destinataris.length === 0 ? 'Afegeix almenys un destinatari.' : '');
  }

  function afegirDestinatari(destinatari: ContacteCorreu): void {
    const email = destinatari.email.trim();

    if (!email || !esEmailValid(email)) {
      return;
    }

    const duplicat = destinataris.some((existent) => existent.email.toLowerCase() === email.toLowerCase());

    if (duplicat) {
      input.value = '';
      estat.textContent = 'Aquest destinatari ja està afegit.';
      return;
    }

    destinataris.push({
      nom: destinatari.nom,
      email,
    });

    renderitzarDestinataris();

    input.value = '';
    estat.textContent = '';
    resultats = [];
    indexActiu = -1;
    mostrarSuggeriments(false);
    actualitzarValidacio();
    input.focus();
  }

  function eliminarDestinatari(email: string): void {
    const index = destinataris.findIndex((destinatari) => destinatari.email === email);

    if (index === -1) {
      return;
    }

    destinataris.splice(index, 1);
    renderitzarDestinataris();
    actualitzarValidacio();
    input.focus();
  }

  function renderitzarDestinataris(): void {
    camp.querySelectorAll('.destinatari-seleccionat').forEach((element) => {
      element.remove();
    });

    destinataris.forEach((destinatari) => {
      const xip = crearElement('span', 'destinatari-seleccionat badge text-bg-secondary d-inline-flex align-items-center gap-2');

      const text = crearElement('span', '', destinatari.nom || destinatari.email);

      const botoEliminar = crearElement('button', 'btn-close btn-close-white');
      botoEliminar.type = 'button';
      botoEliminar.setAttribute('aria-label', `Eliminar ${destinatari.email}`);

      botoEliminar.addEventListener('click', () => {
        eliminarDestinatari(destinatari.email);
      });

      xip.append(text, botoEliminar);
      camp.insertBefore(xip, input);
    });
  }
  function seleccionarContacte(contacte: Contacte): void {
    if (!contacte.email?.trim()) {
      return;
    }

    afegirDestinatari({
      nom: obtenirNom(contacte),
      email: contacte.email.trim(),
    });
  }

  function renderitzarSuggeriments(): void {
    suggeriments.replaceChildren();

    resultats.forEach((contacte, index) => {
      const boto = crearElement('button', 'list-group-item list-group-item-action text-start');

      boto.type = 'button';
      boto.setAttribute('role', 'option');
      boto.setAttribute('aria-selected', String(index === indexActiu));

      if (index === indexActiu) {
        boto.classList.add('active');
      }

      const nom = crearElement('div', 'fw-semibold', obtenirNom(contacte));

      const email = crearElement('small', 'd-block', contacte.email ?? '');

      boto.append(nom, email);

      boto.addEventListener('mousedown', (event) => {
        event.preventDefault();
      });

      boto.addEventListener('click', () => {
        seleccionarContacte(contacte);
      });

      suggeriments.appendChild(boto);
    });

    mostrarSuggeriments(resultats.length > 0);
  }

  async function executarCerca(terme: string): Promise<void> {
    const identificadorActual = ++identificadorCerca;

    if (terme.length < 2) {
      resultats = [];
      indexActiu = -1;
      mostrarSuggeriments(false);
      estat.textContent = '';
      return;
    }

    estat.textContent = 'Cercant contactes...';

    try {
      const contactes = await cercarContactes(terme);

      if (identificadorActual !== identificadorCerca) {
        return;
      }

      resultats = contactes.filter((contacte) => typeof contacte.email === 'string' && contacte.email.trim() !== '');

      indexActiu = -1;

      renderitzarSuggeriments();

      estat.textContent = resultats.length === 0 ? 'No s’han trobat contactes.' : '';
    } catch (error: unknown) {
      if (identificadorActual !== identificadorCerca) {
        return;
      }

      resultats = [];
      mostrarSuggeriments(false);

      estat.textContent = error instanceof Error ? error.message : 'No s’han pogut cercar els contactes.';
    }
  }

  function afegirEmailManual(): void {
    const email = input.value.trim().replace(/,$/, '').trim();

    if (!email) {
      return;
    }

    if (!esEmailValid(email)) {
      estat.textContent = 'Introdueix una adreça de correu vàlida.';
      return;
    }

    afegirDestinatari({
      nom: '',
      email,
    });
  }

  input.addEventListener('input', () => {
    const valor = input.value.trim();

    if (temporitzador !== undefined) {
      clearTimeout(temporitzador);
    }

    if (valor.endsWith(',')) {
      afegirEmailManual();
      return;
    }

    temporitzador = setTimeout(() => {
      void executarCerca(valor);
    }, TEMPS_ESPERA_CERCA);
  });

  input.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowDown' && resultats.length > 0) {
      event.preventDefault();

      indexActiu = Math.min(indexActiu + 1, resultats.length - 1);
      renderitzarSuggeriments();
      return;
    }

    if (event.key === 'ArrowUp' && resultats.length > 0) {
      event.preventDefault();

      indexActiu = Math.max(indexActiu - 1, 0);
      renderitzarSuggeriments();
      return;
    }

    if (event.key === 'Enter') {
      event.preventDefault();

      if (indexActiu >= 0 && resultats[indexActiu]) {
        seleccionarContacte(resultats[indexActiu]);
      } else {
        afegirEmailManual();
      }

      return;
    }

    if (event.key === 'Escape') {
      mostrarSuggeriments(false);
      indexActiu = -1;
    }

    if (event.key === 'Backspace' && input.value === '' && destinataris.length > 0) {
      eliminarDestinatari(destinataris[destinataris.length - 1].email);
    }
  });

  input.addEventListener('blur', () => {
    afegirEmailManual();
    mostrarSuggeriments(false);
  });

  return {
    contenidor,

    obtenirDestinataris: () => destinataris.map((destinatari) => ({ ...destinatari })),

    netejar: () => {
      destinataris.length = 0;
      input.value = '';
      estat.textContent = '';
      resultats = [];
      indexActiu = -1;

      if (temporitzador !== undefined) {
        clearTimeout(temporitzador);
      }

      identificadorCerca++;
      mostrarSuggeriments(false);
      renderitzarDestinataris();
      actualitzarValidacio();
    },

    validar: () => {
      actualitzarValidacio();
      return input.checkValidity();
    },
  };
}
