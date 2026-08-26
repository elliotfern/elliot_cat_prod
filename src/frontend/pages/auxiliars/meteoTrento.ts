import { DOMAIN_WEB } from '../../utils/urls';

export async function obtenerTemperaturaTrento(): Promise<string> {
  const url = `${DOMAIN_WEB}/api/meteo/get`;

  try {
    const response = await fetch(url);

    if (!response.ok) {
      throw new Error(`Error HTTP: ${response.status}`);
    }

    const resultado = await response.json();

    const estacion = resultado.data?.estacion;
    const temperatura = resultado.data?.temperatura;
    const datetime = resultado.data?.datetime;
    const unidad = resultado.data?.unidad ?? '°C';

    if (!temperatura || !datetime || !estacion) {
      return `
        <div class="temperatura-trento lh-sm">
          <div class="fs-2 fw-semibold">-</div>
          <div class="fs-5">-</div>
        </div>
      `;
    }

    // Convertir +01 a +01:00 para que Date lo interprete correctamente
    const fecha = new Date(datetime.replace(/([+-]\d{2})$/, '$1:00'));

    const hora = fecha.toLocaleTimeString('it-IT', {
      hour: '2-digit',
      minute: '2-digit',
      timeZone: 'Europe/Rome',
    });

    return `
      <div class="temperatura-trento lh-sm">
        <div class="fs-2 fw-semibold">${temperatura} ${unidad}</div>
        <div class="fs-5">${estacion}</div>
        <small class="text-muted">${hora}</small>
      </div>
    `;
  } catch (error) {
    console.error('No se ha podido obtener la temperatura:', error);

    return `
      <div class="temperatura-trento lh-sm">
        <div class="fs-2 fw-semibold">-</div>
        <div class="fs-5">-</div>
      </div>
    `;
  }
}
