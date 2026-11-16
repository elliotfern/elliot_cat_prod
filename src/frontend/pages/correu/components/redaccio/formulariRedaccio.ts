import type { IdentificadorMissatgeCorreu, EsborranyPerGuardar, MissatgePerEnviar } from '../../types/Correu';

import type { OpcionsRedaccio } from '../../types/Redaccio';

import { crearElement } from '../../utils/dom';
import { crearEditorRedaccio } from './editorRedaccio';
import { crearCampDestinataris } from './campDestinataris';

export type AccioGuardarEsborrany = (esborrany: EsborranyPerGuardar) => Promise<IdentificadorMissatgeCorreu>;

export type AccioEnviarEsborrany = (id: IdentificadorMissatgeCorreu) => Promise<void>;

const MAX_ADJUNTS = 10;
const MAX_MIDA_ADJUNT = 10 * 1024 * 1024;
const MAX_MIDA_TOTAL_ADJUNTS = 25 * 1024 * 1024;

function escaparHtml(text: string): string {
  return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function convertirTextAHtml(text: string): string {
  return text.split(/\r?\n/).map(escaparHtml).join('<br>');
}

function aplicarTipografiaEmail(html: string): string {
  if (html.trim() === '') {
    return html;
  }

  const contenidor = document.createElement('div');
  contenidor.innerHTML = html;

  const primerElement = contenidor.firstElementChild;

  // Si ya existe un único elemento raíz, reutilizamos su estilo.
  // Esto evita añadir envoltorios repetidos al editar borradores.
  if (contenidor.childElementCount === 1 && primerElement instanceof HTMLElement) {
    primerElement.style.fontFamily = 'Arial, Helvetica, sans-serif';

    return contenidor.innerHTML;
  }

  // Si hay varios elementos, los agrupamos sin alterar su contenido.
  const envoltori = document.createElement('div');
  envoltori.style.fontFamily = 'Arial, Helvetica, sans-serif';

  while (contenidor.firstChild) {
    envoltori.appendChild(contenidor.firstChild);
  }

  return envoltori.outerHTML;
}

function formatarMida(mida: number): string {
  if (mida < 1024 * 1024) {
    return `${(mida / 1024).toFixed(1)} KB`;
  }

  return `${(mida / (1024 * 1024)).toFixed(2)} MB`;
}

export function crearFormulariRedaccio(enviar: (missatge: MissatgePerEnviar, adjunts: File[]) => Promise<void>, opcions: OpcionsRedaccio = {}, guardarEsborrany?: AccioGuardarEsborrany, enviarEsborrany?: AccioEnviarEsborrany): HTMLFormElement {
  const formulari = crearElement('form', 'd-flex flex-column gap-3');

  const destinataris = crearCampDestinataris('redaccio-destinataris', 'Per a', true, 'col-12');
  const copia = crearCampDestinataris('redaccio-copia', 'CC');
  const copiaOculta = crearCampDestinataris('redaccio-copia-oculta', 'CCO');

  if (opcions.destinataris) {
    destinataris.establirDestinataris(opcions.destinataris);
  }

  if (opcions.copia) {
    copia.establirDestinataris(opcions.copia);
  }

  if (opcions.copiaOculta) {
    copiaOculta.establirDestinataris(opcions.copiaOculta);
  }

  const assumpteContenidor = crearElement('div', 'mb-0');
  const assumpteLabel = crearElement('label', 'form-label', 'Assumpte');

  assumpteLabel.htmlFor = 'redaccio-assumpte';

  const assumpte = crearElement('input', 'form-control');
  assumpte.type = 'text';
  assumpte.id = 'redaccio-assumpte';
  assumpte.placeholder = 'Assumpte del missatge';
  assumpte.value = opcions.assumpte ?? '';

  assumpteContenidor.append(assumpteLabel, assumpte);

  const editor = crearEditorRedaccio('redaccio-contingut');

  const contingutInicial = opcions.contingutInicialHtml ?? (opcions.contingutInicialText ? convertirTextAHtml(opcions.contingutInicialText) : '');

  if (contingutInicial !== '') {
    requestAnimationFrame(() => {
      editor.establirContingut(contingutInicial);
    });
  }

  // Selector d'arxius adjunts.
  const adjuntsContenidor = crearElement('div', 'mb-0');

  const adjuntsLabel = crearElement('label', 'form-label', 'Fitxers adjunts');
  adjuntsLabel.htmlFor = 'redaccio-adjunts';

  const adjuntsInput = crearElement('input', 'form-control');
  adjuntsInput.type = 'file';
  adjuntsInput.id = 'redaccio-adjunts';
  adjuntsInput.multiple = true;

  // Els adjunts als esborranys encara no estan implementats.
  adjuntsInput.disabled = Boolean(opcions.idEsborrany);

  const adjuntsAjuda = crearElement('div', 'form-text', 'Màxim 10 fitxers, 10 MB per fitxer i 25 MB en total.');

  const adjuntsLlista = crearElement('div', 'list-group mt-2');

  adjuntsContenidor.append(adjuntsLabel, adjuntsInput, adjuntsAjuda, adjuntsLlista);

  const estat = crearElement('div', 'small');
  estat.setAttribute('role', 'status');
  estat.setAttribute('aria-live', 'polite');

  const botoEnviar = crearElement('button', 'btn btn-primary', 'Enviar');
  botoEnviar.type = 'submit';

  const accions = crearElement('div', 'd-flex justify-content-between align-items-center gap-2');

  const botoGuardar = guardarEsborrany ? crearElement('button', 'btn btn-outline-secondary', 'Guardar borrador') : null;

  if (botoGuardar) {
    botoGuardar.type = 'button';

    botoGuardar.addEventListener('click', () => {
      void executarGuardatEsborrany();
    });

    accions.appendChild(botoGuardar);
  }

  accions.appendChild(botoEnviar);

  const filaDestinataris = crearElement('div', 'row g-3');

  filaDestinataris.append(destinataris.contenidor, copia.contenidor, copiaOculta.contenidor);

  formulari.append(filaDestinataris, assumpteContenidor, editor.contenidor, adjuntsContenidor, estat, accions);

  // Conservamos el identificador cuando estamos editando un borrador.
  let identificadorEsborrany: IdentificadorMissatgeCorreu | undefined = opcions.idEsborrany;

  // Archivos seleccionados para el envío.
  let adjunts: File[] = [];

  const teContingutCitat = Boolean(opcions.contingutCitatText?.trim() || opcions.contingutCitatHtml?.trim());

  function obtenirMidaTotalAdjunts(): number {
    return adjunts.reduce((total, adjunt) => total + adjunt.size, 0);
  }

  function renderitzarAdjunts(): void {
    adjuntsLlista.replaceChildren();

    adjunts.forEach((adjunt, index) => {
      const fila = crearElement('div', 'list-group-item d-flex justify-content-between align-items-center gap-3');

      const informacio = crearElement('div', 'd-flex flex-column overflow-hidden');

      const nom = crearElement('span', 'text-break', adjunt.name);
      const mida = crearElement('small', 'text-body-secondary', formatarMida(adjunt.size));

      informacio.append(nom, mida);

      const botoEliminar = crearElement('button', 'btn btn-sm btn-outline-danger', 'Eliminar');
      botoEliminar.type = 'button';
      botoEliminar.setAttribute('aria-label', `Eliminar ${adjunt.name}`);

      botoEliminar.addEventListener('click', () => {
        adjunts.splice(index, 1);
        renderitzarAdjunts();

        estat.className = 'small text-body-secondary';
        estat.textContent = '';
      });

      fila.append(informacio, botoEliminar);
      adjuntsLlista.appendChild(fila);
    });
  }

  adjuntsInput.addEventListener('change', () => {
    const fitxersSeleccionats = Array.from(adjuntsInput.files ?? []);

    let rebutjats = 0;

    for (const fitxer of fitxersSeleccionats) {
      const duplicat = adjunts.some((existent) => existent.name === fitxer.name && existent.size === fitxer.size && existent.lastModified === fitxer.lastModified);

      if (duplicat) {
        continue;
      }

      if (adjunts.length >= MAX_ADJUNTS) {
        rebutjats++;
        continue;
      }

      if (fitxer.size > MAX_MIDA_ADJUNT) {
        rebutjats++;
        continue;
      }

      if (obtenirMidaTotalAdjunts() + fitxer.size > MAX_MIDA_TOTAL_ADJUNTS) {
        rebutjats++;
        continue;
      }

      adjunts.push(fitxer);
    }

    // Permite volver a seleccionar el mismo archivo si se ha eliminado.
    adjuntsInput.value = '';

    renderitzarAdjunts();

    if (rebutjats > 0) {
      estat.className = 'small text-danger';
      estat.textContent = `${rebutjats} fitxer(s) no s'han pogut afegir. Revisa els límits de quantitat i mida.`;
    } else {
      estat.className = 'small text-body-secondary';
      estat.textContent = '';
    }
  });

  function obtenirDadesEsborrany(): EsborranyPerGuardar {
    const contingutHtml = aplicarTipografiaEmail(editor.obtenirContingut());

    return {
      ...(identificadorEsborrany ? { id: identificadorEsborrany } : {}),
      destinataris: destinataris.obtenirDestinataris(),
      copia: copia.obtenirDestinataris(),
      copia_oculta: copiaOculta.obtenirDestinataris(),
      assumpte: assumpte.value.trim(),
      contingut_text: null,
      contingut_html: contingutHtml.trim() !== '' ? contingutHtml : null,
    };
  }

  async function executarGuardatEsborrany(): Promise<void> {
    if (!guardarEsborrany || !botoGuardar) {
      return;
    }

    if (adjunts.length > 0) {
      estat.className = 'small text-danger';
      estat.textContent = 'Encara no es poden desar fitxers adjunts als esborranys.';
      return;
    }

    botoGuardar.disabled = true;
    botoEnviar.disabled = true;

    estat.className = 'small text-body-secondary';
    estat.textContent = 'Guardant l’esborrany...';

    try {
      identificadorEsborrany = await guardarEsborrany(obtenirDadesEsborrany());

      // Un cop desat, el formulari passa a editar un esborrany existent.
      adjuntsInput.disabled = true;

      estat.className = 'small text-success';
      estat.textContent = 'Esborrany guardat correctament.';
    } catch (error: unknown) {
      estat.className = 'small text-danger';
      estat.textContent = error instanceof Error ? error.message : 'No s’ha pogut guardar l’esborrany.';
    } finally {
      botoGuardar.disabled = false;
      botoEnviar.disabled = false;
    }
  }

  formulari.addEventListener('submit', async (event) => {
    event.preventDefault();

    destinataris.validar();
    copia.validar();
    copiaOculta.validar();

    if (!formulari.reportValidity()) {
      return;
    }

    const contingutHtml = aplicarTipografiaEmail(editor.obtenirContingut());

    const comprovacioContingut = document.createElement('div');
    comprovacioContingut.innerHTML = contingutHtml;

    const teContingutNou = Boolean(comprovacioContingut.textContent?.trim());

    if (!teContingutNou && !teContingutCitat) {
      estat.className = 'small text-danger';
      estat.textContent = 'Escriu el contingut del missatge.';
      return;
    }

    botoEnviar.disabled = true;

    if (botoGuardar) {
      botoGuardar.disabled = true;
    }

    estat.className = 'small text-body-secondary';
    estat.textContent = 'Enviant el missatge...';

    try {
      if (identificadorEsborrany) {
        if (!guardarEsborrany || !enviarEsborrany) {
          throw new Error('No se ha podido configurar el envío del borrador.');
        }

        // Guardamos primero los últimos cambios.
        identificadorEsborrany = await guardarEsborrany(obtenirDadesEsborrany());

        // Enviamos exactamente la versión que acabamos de guardar.
        await enviarEsborrany(identificadorEsborrany);
      } else {
        // Correo nuevo, respuesta o reenvío.
        const missatge: MissatgePerEnviar = {
          destinataris: destinataris.obtenirDestinataris(),
          copia: copia.obtenirDestinataris(),
          copia_oculta: copiaOculta.obtenirDestinataris(),
          assumpte: assumpte.value.trim(),
          contingut_text: null,
          contingut_html: contingutHtml,
          in_reply_to: opcions.inReplyTo ?? null,
          referencies: opcions.referencies ?? [],
          contingut_citat_text: opcions.contingutCitatText ?? null,
          contingut_citat_html: opcions.contingutCitatHtml ?? null,
        };

        await enviar(missatge, adjunts);
      }

      formulari.reset();

      destinataris.netejar();
      copia.netejar();
      copiaOculta.netejar();

      editor.netejar();

      adjunts = [];
      renderitzarAdjunts();
      adjuntsInput.disabled = false;

      identificadorEsborrany = undefined;

      estat.className = 'small text-success';
      estat.textContent = 'Missatge enviat correctament.';
    } catch (error: unknown) {
      estat.className = 'small text-danger';
      estat.textContent = error instanceof Error ? error.message : "No s'ha pogut enviar el missatge.";
    } finally {
      botoEnviar.disabled = false;

      if (botoGuardar) {
        botoGuardar.disabled = false;
      }
    }
  });

  return formulari;
}
