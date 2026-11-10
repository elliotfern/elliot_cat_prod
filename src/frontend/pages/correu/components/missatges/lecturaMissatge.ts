import DOMPurify from 'dompurify';

import { crearElement } from '../../utils/dom';
import { formatarData } from '../../utils/formatData';

import type { MissatgeDetall } from '../../types/Correu';

export interface AccionsLectura {
  respondre?: (missatge: MissatgeDetall) => void;
  respondreTots?: (missatge: MissatgeDetall) => void;
  reenviar?: (missatge: MissatgeDetall) => void;
  editarEsborrany?: (missatge: MissatgeDetall) => void;
  eliminarEsborrany?: (missatge: MissatgeDetall) => void;
}

export function renderitzarLectura(contenidor: HTMLElement, missatge: MissatgeDetall, accions?: AccionsLectura): void {
  contenidor.replaceChildren();

  const capcalera = crearElement('div', 'border-bottom pb-3 mb-3');

  const assumpte = crearElement('h5', 'mb-3', missatge.assumpte || '(Sense assumpte)');

  const remitent = crearElement('div', 'fw-semibold', `${missatge.remitent.nom} <${missatge.remitent.email}>`);

  const destinataris = crearElement('div', 'small text-body-secondary mt-1', `Per a: ${missatge.destinataris.map((d) => d.email).join(', ')}`);

  const data = crearElement('div', 'small text-body-secondary mt-1', formatarData(missatge.data));

  capcalera.append(assumpte, remitent, destinataris, data);

  const cos = crearElement('div', 'correu-contingut');

  if (missatge.contingut_html) {
    const html = DOMPurify.sanitize(missatge.contingut_html, {
      USE_PROFILES: { html: true },
    });

    const contingut = crearElement('div', 'correu-contingut-html');
    contingut.innerHTML = html;

    cos.appendChild(contingut);
  } else if (missatge.contingut_text) {
    const text = crearElement('pre', 'text-wrap mb-0');
    text.textContent = missatge.contingut_text;

    cos.appendChild(text);
  } else {
    cos.textContent = 'Aquest missatge no té contingut disponible.';
  }

  contenidor.appendChild(capcalera);

  if (accions) {
    const barraAccions = crearElement('div', 'd-flex flex-wrap gap-2 mb-3');

    if (accions.editarEsborrany) {
      const botoEditar = crearElement('button', 'btn btn-outline-primary btn-sm', 'Editar borrador');

      botoEditar.type = 'button';

      botoEditar.addEventListener('click', () => {
        accions.editarEsborrany?.(missatge);
      });

      barraAccions.appendChild(botoEditar);
    }

    if (accions.eliminarEsborrany) {
      const botoEliminar = crearElement('button', 'btn btn-outline-danger btn-sm', 'Eliminar borrador');

      botoEliminar.type = 'button';

      botoEliminar.addEventListener('click', () => {
        accions.eliminarEsborrany?.(missatge);
      });

      barraAccions.appendChild(botoEliminar);
    }

    if (accions.respondre) {
      const botoRespondre = crearElement('button', 'btn btn-outline-primary btn-sm', 'Respondre');

      botoRespondre.type = 'button';

      botoRespondre.addEventListener('click', () => {
        accions.respondre?.(missatge);
      });

      barraAccions.appendChild(botoRespondre);
    }

    if (accions.respondreTots) {
      const botoRespondreTots = crearElement('button', 'btn btn-outline-primary btn-sm', 'Respondre a tots');

      botoRespondreTots.type = 'button';

      botoRespondreTots.addEventListener('click', () => {
        accions.respondreTots?.(missatge);
      });

      barraAccions.appendChild(botoRespondreTots);
    }

    if (accions.reenviar) {
      const botoReenviar = crearElement('button', 'btn btn-outline-secondary btn-sm', 'Reenviar');

      botoReenviar.type = 'button';

      botoReenviar.addEventListener('click', () => {
        accions.reenviar?.(missatge);
      });

      barraAccions.appendChild(botoReenviar);
    }

    if (barraAccions.childElementCount > 0) {
      contenidor.appendChild(barraAccions);
    }
  }

  contenidor.appendChild(cos);
}
