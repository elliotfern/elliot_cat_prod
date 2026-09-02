export function formataHTML(texto: string): string {
  const temp = document.createElement('div');
  temp.innerHTML = texto;
  return temp.textContent || temp.innerText || '';
}
