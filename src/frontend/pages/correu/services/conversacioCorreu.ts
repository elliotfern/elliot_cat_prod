import DOMPurify from 'dompurify';

import type { ContacteCorreu, MissatgeDetall } from '../types/Correu';

import type { OpcionsRedaccio } from '../types/Redaccio';

import { formatarData } from '../utils/formatData';

const ADRECA_PROPIA = 'elliot@hispantic.com';

function normalitzarEmail(email: string): string {
  return email.trim().toLowerCase();
}

function escaparHtml(text: string): string {
  return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function obtenirHtmlOriginal(missatge: MissatgeDetall): string {
  if (missatge.contingut_html) {
    return DOMPurify.sanitize(missatge.contingut_html, {
      USE_PROFILES: { html: true },
    });
  }

  return `<pre>${escaparHtml(missatge.contingut_text ?? '')}</pre>`;
}

function obtenirTextOriginal(missatge: MissatgeDetall): string {
  if (missatge.contingut_text?.trim()) {
    return missatge.contingut_text;
  }

  const documentOriginal = new DOMParser().parseFromString(obtenirHtmlOriginal(missatge), 'text/html');

  return documentOriginal.body.textContent ?? '';
}

function obtenirContingutCitat(
  missatge: MissatgeDetall,
  reenviament = false
): {
  text: string;
  html: string;
} {
  const htmlOriginal = obtenirHtmlOriginal(missatge);
  const textOriginal = obtenirTextOriginal(missatge);

  const remitent = missatge.remitent.nom ? `${missatge.remitent.nom} <${missatge.remitent.email}>` : missatge.remitent.email;

  if (!reenviament) {
    const introduccio = `El ${formatarData(missatge.data)}, ${remitent} escribió:`;

    const textCitat = textOriginal
      .split(/\r?\n/)
      .map((linia) => `> ${linia}`)
      .join('\n');

    return {
      text: `${introduccio}\n${textCitat}`,
      html: `
        <blockquote type="cite">
          <p>${escaparHtml(introduccio)}</p>
          ${htmlOriginal}
        </blockquote>
      `,
    };
  }

  const destinataris = missatge.destinataris.map((destinatari) => destinatari.email).join(', ');

  const copia = missatge.copia.map((destinatari) => destinatari.email).join(', ');

  const capcalera = ['---------- Mensaje reenviado ----------', `De: ${remitent}`, `Fecha: ${formatarData(missatge.data)}`, ...(destinataris ? [`Para: ${destinataris}`] : []), ...(copia ? [`CC: ${copia}`] : []), `Asunto: ${missatge.assumpte}`];

  const capcaleraHtml = capcalera.map((linia) => `<p>${escaparHtml(linia)}</p>`).join('');

  return {
    text: `${capcalera.join('\n')}\n\n${textOriginal}`,
    html: `
      <div class="correu-reenviament">
        <hr>
        ${capcaleraHtml}
        ${htmlOriginal}
      </div>
    `,
  };
}

function obtenirReferenciesResposta(missatge: MissatgeDetall): string[] {
  const referencies = missatge.referencies.length > 0 ? missatge.referencies : missatge.in_reply_to ? [missatge.in_reply_to] : [];

  return [...new Set(referencies)];
}

function afegirPrefixAssumpte(assumpte: string, prefix: 'Re' | 'Fwd'): string {
  const assumpteNet = assumpte.trim();

  const prefixRegex = prefix === 'Re' ? /^re\s*:/i : /^fwd\s*:/i;

  return prefixRegex.test(assumpteNet) ? assumpteNet : `${prefix}: ${assumpteNet}`;
}

function afegirDestinatariUnic(resultat: ContacteCorreu[], vistos: Set<string>, destinatari: ContacteCorreu): void {
  const email = destinatari.email.trim();
  const emailNormalitzat = normalitzarEmail(email);

  if (!email || emailNormalitzat === normalitzarEmail(ADRECA_PROPIA) || vistos.has(emailNormalitzat)) {
    return;
  }

  vistos.add(emailNormalitzat);

  resultat.push({
    nom: destinatari.nom,
    email,
  });
}

/**
 * Prepara una resposta al remitent original.
 */
export function crearOpcionsResposta(missatge: MissatgeDetall): OpcionsRedaccio {
  const destinataris: ContacteCorreu[] = [];
  const vistos = new Set<string>();

  const remitentEsPropi = normalitzarEmail(missatge.remitent.email) === normalitzarEmail(ADRECA_PROPIA);

  if (remitentEsPropi) {
    for (const destinatari of missatge.destinataris) {
      afegirDestinatariUnic(destinataris, vistos, destinatari);
    }
  } else {
    afegirDestinatariUnic(destinataris, vistos, missatge.remitent);
  }

  const citat = obtenirContingutCitat(missatge);

  return {
    destinataris,
    assumpte: afegirPrefixAssumpte(missatge.assumpte, 'Re'),
    inReplyTo: missatge.message_id,
    referencies: obtenirReferenciesResposta(missatge),
    contingutCitatText: citat.text,
    contingutCitatHtml: citat.html,
  };
}

/**
 * Prepara una resposta a tots els destinataris originals.
 */
export function crearOpcionsRespostaTots(missatge: MissatgeDetall): OpcionsRedaccio {
  const destinataris: ContacteCorreu[] = [];
  const copia: ContacteCorreu[] = [];

  const vistos = new Set<string>();

  const remitentEsPropi = normalitzarEmail(missatge.remitent.email) === normalitzarEmail(ADRECA_PROPIA);

  if (!remitentEsPropi) {
    afegirDestinatariUnic(destinataris, vistos, missatge.remitent);
  }

  for (const destinatari of missatge.destinataris) {
    afegirDestinatariUnic(destinataris, vistos, destinatari);
  }

  for (const destinatari of missatge.copia) {
    afegirDestinatariUnic(copia, vistos, destinatari);
  }

  const citat = obtenirContingutCitat(missatge);

  return {
    destinataris,
    copia,
    assumpte: afegirPrefixAssumpte(missatge.assumpte, 'Re'),
    inReplyTo: missatge.message_id,
    referencies: obtenirReferenciesResposta(missatge),
    contingutCitatText: citat.text,
    contingutCitatHtml: citat.html,
  };
}

/**
 * Prepara un reenviament del missatge original.
 */
export function crearOpcionsReenviament(missatge: MissatgeDetall): OpcionsRedaccio {
  const citat = obtenirContingutCitat(missatge, true);

  return {
    assumpte: afegirPrefixAssumpte(missatge.assumpte, 'Fwd'),
    contingutCitatText: citat.text,
    contingutCitatHtml: citat.html,
  };
}
