export function formatarData(data: string): string {
  const valor = new Date(data);

  if (Number.isNaN(valor.getTime())) {
    return data;
  }

  return new Intl.DateTimeFormat('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(valor);
}
