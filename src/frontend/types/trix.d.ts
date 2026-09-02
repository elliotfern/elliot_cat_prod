// src/types/trix.d.ts

declare global {
  interface HTMLTrixEditorElement extends HTMLElement {
    editor: TrixEditor;
    toolbarElement: HTMLElement;
  }

  interface Window {
    dayjs: typeof import('dayjs');
    __demanarRecepta: (id: string, button: HTMLButtonElement) => void;
  }
  interface TrixEditor {
    loadHTML(html: string): void;
    insertString(text: string): void;
    insertHTML(html: string): void;
    getDocument(): TrixDocument;
    setSelectedRange(range: [number, number]): void;
    getSelectedRange(): [number, number];
    deleteInDirection(direction: 'backward' | 'forward'): void;
  }

  interface TrixDocument {
    toString(): string;
  }

  interface TrixAttachment {
    file?: File;
    url?: string;
    contentType?: string;
  }

  interface TrixChangeEvent extends Event {
    target: HTMLTrixEditorElement;
  }

  interface TrixAttachmentEvent extends Event {
    attachment: TrixAttachment;
    target: HTMLTrixEditorElement;
  }

  // 👇 aquí no redefinimos HTMLElementEventMap, lo extendemos
  interface HTMLElementEventMap {
    'trix-change': TrixChangeEvent;
    'trix-initialize': Event;
    'trix-focus': Event;
    'trix-blur': Event;
    'trix-attachment-add': TrixAttachmentEvent;
    'trix-attachment-remove': TrixAttachmentEvent;
  }
}

export {};
