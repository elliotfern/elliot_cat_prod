export function mostrar(valor: any, fallback: string = ''): string {
  return valor == null || valor === '' ? fallback : String(valor);
}
