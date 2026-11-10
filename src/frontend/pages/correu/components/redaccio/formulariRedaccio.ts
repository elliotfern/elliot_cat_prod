import type { IdentificadorMissatgeCorreu, EsborranyPerGuardar, MissatgePerEnviar } from '../../types/Correu';

import type { OpcionsRedaccio } from '../../types/Redaccio';

import { crearElement } from '../../utils/dom';
import { crearEditorRedaccio } from './editorRedaccio';
import { crearCampDestinataris } from './campDestinataris';

export type AccioGuardarEsborrany = (esborrany: EsborranyPerGuardar) => Promise<IdentificadorMissatgeCorreu>;

export function crearFormulariRedaccio(enviar: (missatge: MissatgePerEnviar) => Promise<void>, opcions: OpcionsRedaccio = {}, guardarEsborrany?: AccioGuardarEsborrany): HTMLFormElement {
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

  formulari.append(filaDestinataris, assumpteContenidor, editor.contenidor, estat, accions);

  let identificadorEsborrany: IdentificadorMissatgeCorreu | undefined;

  const teContingutCitat = Boolean(opcions.contingutCitatText?.trim() || opcions.contingutCitatHtml?.trim());

  async function executarGuardatEsborrany(): Promise<void> {
    if (!guardarEsborrany || !botoGuardar) {
      return;
    }

    const contingutHtml = editor.obtenirContingut();

    const esborrany: EsborranyPerGuardar = {
      ...(identificadorEsborrany ? { id: identificadorEsborrany } : {}),
      destinataris: destinataris.obtenirDestinataris(),
      copia: copia.obtenirDestinataris(),
      copia_oculta: copiaOculta.obtenirDestinataris(),
      assumpte: assumpte.value.trim(),
      contingut_text: null,
      contingut_html: contingutHtml.trim() !== '' ? contingutHtml : null,
    };

    botoGuardar.disabled = true;
    botoEnviar.disabled = true;

    estat.className = 'small text-body-secondary';
    estat.textContent = 'Guardant l’esborrany...';

    try {
      identificadorEsborrany = await guardarEsborrany(esborrany);

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

    const contingutHtml = editor.obtenirContingut();

    const comprovacioContingut = document.createElement('div');
    comprovacioContingut.innerHTML = contingutHtml;

    const teContingutNou = Boolean(comprovacioContingut.textContent?.trim());

    if (!teContingutNou && !teContingutCitat) {
      estat.className = 'small text-danger';
      estat.textContent = 'Escriu el contingut del missatge.';
      return;
    }

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

    botoEnviar.disabled = true;

    if (botoGuardar) {
      botoGuardar.disabled = true;
    }

    estat.className = 'small text-body-secondary';
    estat.textContent = 'Enviant el missatge...';

    try {
      await enviar(missatge);

      formulari.reset();

      destinataris.netejar();
      copia.netejar();
      copiaOculta.netejar();

      editor.netejar();

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
