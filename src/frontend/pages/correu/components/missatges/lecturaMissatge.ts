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
  eliminarMissatge?: (missatge: MissatgeDetall) => void;
  descarregarAdjunt?: (missatge: MissatgeDetall, identificadorAdjunt: string) => void;
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

    const iframe = document.createElement('iframe');

    iframe.title = 'Contingut del correu electrònic';
    iframe.setAttribute('sandbox', 'allow-same-origin');
    iframe.setAttribute('referrerpolicy', 'no-referrer');

    iframe.className = 'w-100 border-0';
    iframe.style.display = 'block';
    iframe.style.height = '300px';

    const estilsAdaptatius = `
      html,
      body {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
      }

      body {
        margin: 0 !important;
        overflow-wrap: anywhere;
      }

      img {
        max-width: 100% !important;
        height: auto !important;
      }

      table {
        max-width: 100% !important;
      }

      td,
      th {
        overflow-wrap: anywhere;
      }
    `;

    iframe.srcdoc = `
      <!DOCTYPE html>
      <html lang="und">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1">
        </head>
        <body>
          ${html}
          <style>${estilsAdaptatius}</style>
        </body>
      </html>
    `;

    iframe.addEventListener('load', () => {
      const documentIframe = iframe.contentDocument;

      if (!documentIframe) {
        return;
      }

      const ajustarAlcada = (): void => {
        const alcada = documentIframe.documentElement.scrollHeight;

        iframe.style.height = `${Math.max(150, alcada)}px`;
      };

      ajustarAlcada();

      const observador = new ResizeObserver(ajustarAlcada);

      observador.observe(documentIframe.documentElement);

      if (documentIframe.body) {
        observador.observe(documentIframe.body);
      }

      documentIframe.querySelectorAll('img').forEach((imatge) => {
        imatge.addEventListener('load', ajustarAlcada);
      });

      iframe.addEventListener(
        'DOMNodeRemoved',
        () => {
          observador.disconnect();
        },
        { once: true }
      );
    });

    contingut.appendChild(iframe);
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

    if (accions.eliminarMissatge) {
      const botoEliminar = crearElement('button', 'btn btn-outline-danger btn-sm', 'Eliminar');

      botoEliminar.type = 'button';

      botoEliminar.addEventListener('click', () => {
        accions.eliminarMissatge?.(missatge);
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

  if (missatge.adjunts.length > 0) {
    const seccioAdjunts = crearElement('div', 'border-top mt-4 pt-3');

    const titol = crearElement('h6', 'mb-3', `Archivos adjuntos (${missatge.adjunts.length})`);

    const llista = crearElement('div', 'd-flex flex-column gap-2');

    for (const adjunt of missatge.adjunts) {
      const fila = crearElement('div', 'd-flex align-items-center justify-content-between flex-wrap gap-2 border rounded p-2');

      const informacio = crearElement('div', 'd-flex flex-column');

      const nom = crearElement('span', 'fw-semibold', adjunt.nom_fitxer);

      const mida = crearElement('small', 'text-body-secondary', adjunt.mida !== null ? `${(adjunt.mida / 1024).toFixed(1)} KB` : 'Tamaño desconocido');

      informacio.append(nom, mida);
      fila.appendChild(informacio);

      if (accions?.descarregarAdjunt) {
        const boto = crearElement('button', 'btn btn-outline-primary btn-sm', 'Descargar');

        boto.type = 'button';

        boto.addEventListener('click', () => {
          accions.descarregarAdjunt?.(missatge, adjunt.identificador);
        });

        fila.appendChild(boto);
      }

      llista.appendChild(fila);
    }

    seccioAdjunts.append(titol, llista);
    contenidor.appendChild(seccioAdjunts);
  }
}
