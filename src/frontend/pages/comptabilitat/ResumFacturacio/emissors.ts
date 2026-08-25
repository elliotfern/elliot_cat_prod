import { api } from '../../../core/api/client';
import { Emissor } from './capcalera';

export interface EmissorSelector {
  id: string;
  nom: string;
  dataInici: string;
  dataFi: string;
}

export async function getEmissorsComptabilitat(): Promise<EmissorSelector[]> {
  return api.get<EmissorSelector[]>('comptabilitat/get/emissorsComptabilitat');
}

export async function getEmissor(emissorId: string): Promise<Emissor> {
  return api.get<Emissor>('comptabilitat/get/emissorId', {
    id: emissorId,
  });
}

export function getExercicisDisponibles(emissor: EmissorSelector): number[] {
  const anyInici = Number(emissor.dataInici.substring(0, 4));
  const anyFi = Number(emissor.dataFi.substring(0, 4));
  const anyActual = new Date().getFullYear();

  const anyFinal = Math.min(anyFi, anyActual);

  const exercicis: number[] = [];

  for (let any = anyInici; any <= anyFinal; any++) {
    exercicis.push(any);
  }

  return exercicis.reverse();
}
