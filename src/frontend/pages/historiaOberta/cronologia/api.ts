export async function getEventos(etapa: string, subetapa?: string) {
  const params = new URLSearchParams({ etapa });

  if (subetapa) {
    params.set('subetapa', subetapa);
  }

  const res = await fetch(`/api/historia/get/llistatEsdeveniments?${params.toString()}`);

  return res.json();
}

export async function getSubetapas(etapa: string) {
  const params = new URLSearchParams({ id: etapa });

  const res = await fetch(`/api/historia/get/subEtapesEtapa?${params.toString()}`);

  return res.json();
}

export async function getEtapas() {
  const res = await fetch('/api/historia/get/llistatEtapes');

  if (!res.ok) {
    throw new Error('Error carregant les etapes històriques');
  }

  return res.json();
}
