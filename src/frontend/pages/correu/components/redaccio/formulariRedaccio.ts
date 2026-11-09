import type { MissatgePerEnviar } from '../../types/Correu';

import { crearElement } from '../../utils/dom';
import { crearEditorRedaccio } from './editorRedaccio';
import { crearCampDestinataris } from './campDestinataris';

export function crearFormulariRedaccio(enviar: (missatge: MissatgePerEnviar) => Promise<void>): HTMLFormElement {
  const formulari = crearElement('form', 'd-flex flex-column gap-3');

  const destinataris = crearCampDestinataris('redaccio-destinataris', 'Per a', true, 'col-12');

  const copia = crearCampDestinataris('redaccio-copia', 'CC');

  const copiaOculta = crearCampDestinataris('redaccio-copia-oculta', 'CCO');

  const assumpteContenidor = crearElement('div', 'mb-0');

  const assumpteLabel = crearElement('label', 'form-label', 'Assumpte');
  assumpteLabel.htmlFor = 'redaccio-assumpte';

  const assumpte = crearElement('input', 'form-control');
  assumpte.type = 'text';
  assumpte.id = 'redaccio-assumpte';
  assumpte.placeholder = 'Assumpte del missatge';

  assumpteContenidor.append(assumpteLabel, assumpte);

  const editor = crearEditorRedaccio('redaccio-contingut');

  const estat = crearElement('div', 'small');
  estat.setAttribute('role', 'status');
  estat.setAttribute('aria-live', 'polite');

  const botoEnviar = crearElement('button', 'btn btn-primary', 'Enviar');
  botoEnviar.type = 'submit';

  const accions = crearElement('div', 'd-flex justify-content-end');
  accions.appendChild(botoEnviar);

  const filaDestinataris = crearElement('div', 'row g-3');

  filaDestinataris.append(destinataris.contenidor, copia.contenidor, copiaOculta.contenidor);

  formulari.append(filaDestinataris, assumpteContenidor, editor.contenidor, estat, accions);

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

    if (!comprovacioContingut.textContent?.trim()) {
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
    };

    botoEnviar.disabled = true;
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
    }
  });

  return formulari;
}
