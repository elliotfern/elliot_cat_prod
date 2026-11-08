import { crearElement } from '../../utils/dom';

interface TrixEditorElement extends HTMLElement {
  editor: {
    loadHTML(html: string): void;
  };
}

export interface EditorRedaccio {
  contenidor: HTMLElement;
  obtenirContingut(): string;
  establirContingut(html: string): void;
  netejar(): void;
}

export function crearEditorRedaccio(id: string): EditorRedaccio {
  const contenidor = crearElement('div', 'editor-redaccio');

  const input = crearElement('input');
  input.type = 'hidden';
  input.id = `${id}-input`;

  const editor = document.createElement('trix-editor') as TrixEditorElement;
  editor.setAttribute('input', input.id);
  editor.setAttribute('placeholder', 'Escriu el teu missatge...');
  editor.classList.add('form-control');

  contenidor.append(input, editor);

  return {
    contenidor,

    obtenirContingut(): string {
      return input.value;
    },

    establirContingut(html: string): void {
      editor.editor.loadHTML(html);
    },

    netejar(): void {
      editor.editor.loadHTML('');
    },
  };
}
