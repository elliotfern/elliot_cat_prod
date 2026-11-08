import DOMPurify from 'dompurify';
import { crearElement } from '../../utils/dom';
import { formatarData } from '../../utils/formatData';
import { MissatgeDetall } from '../../types/Correu';

export function renderitzarLectura(contenidor: HTMLElement, missatge: MissatgeDetall): void {
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

  contenidor.append(capcalera, cos);
}
