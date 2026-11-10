import type { ContacteCorreu, IdentificadorMissatgeCorreu } from './Correu';

export interface OpcionsRedaccio {
  destinataris?: ContacteCorreu[];
  copia?: ContacteCorreu[];
  copiaOculta?: ContacteCorreu[];

  assumpte?: string;

  inReplyTo?: string | null;
  referencies?: string[];

  contingutCitatText?: string | null;
  contingutCitatHtml?: string | null;

  // Identificador del borrador que estamos editando.
  idEsborrany?: IdentificadorMissatgeCorreu;

  // Contenido original que se precarga en el editor.
  contingutInicialText?: string | null;
  contingutInicialHtml?: string | null;
}
