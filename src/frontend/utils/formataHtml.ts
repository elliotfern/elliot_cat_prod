export function formataHTML(texto: string): string {
  var temp = document.createElement('div');
  temp.innerHTML = texto;
  return temp.textContent || temp.innerText || '';
}
