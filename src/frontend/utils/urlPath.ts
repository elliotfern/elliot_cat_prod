// Verificar la URL y llamar a las funciones correspondientes

export function getPageType(url: string): string[] {
  const path = new URL(url).pathname; // siempre funciona

  return path.split('/').filter(Boolean);
}
