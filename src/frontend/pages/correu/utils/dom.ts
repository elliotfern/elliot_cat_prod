export function crearElement<K extends keyof HTMLElementTagNameMap>(etiqueta: K, classes = '', text?: string): HTMLElementTagNameMap[K] {
  const element = document.createElement(etiqueta);
  element.className = classes;

  if (text !== undefined) {
    element.textContent = text;
  }

  return element;
}

export function mostrarEstat(contenidor: HTMLElement, missatge: string): void {
  contenidor.replaceChildren(crearElement('div', 'text-center text-body-secondary py-5', missatge));
}
