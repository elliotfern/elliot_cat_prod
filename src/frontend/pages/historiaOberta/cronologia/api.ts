export async function getEventos(etapa: number, subetapa?: string) {
  let url = `/api/historia/get/llistatEsdeveniments?etapa=${etapa}`;
  if (subetapa) url += `&subetapa=${subetapa}`;

  const res = await fetch(url);
  return res.json();
}

export async function getSubetapas(etapa: number) {
  const res = await fetch(`/api/historia/get/subEtapesEtapa?id=${etapa}`);
  return res.json();
}
