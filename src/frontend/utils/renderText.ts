export function mostrar(valor: unknown, fallback: string = ''): string {
  return valor == null || valor === '' ? fallback : String(valor);
}
