import { api, apiSymfony } from '../../../Infrastructure/Api/Client/ApiClient';
import { ENDPOINT_CARPETES, ENDPOINT_CERCAR_CONTACTES, ENDPOINT_ENVIAR, ENDPOINT_LLEGIT, ENDPOINT_MISSATGE, ENDPOINT_MISSATGES } from '../constants/endpoints';
import type { CarpetaCorreu, Contacte, MissatgeDetall, MissatgeLlista, MissatgePerEnviar, RespostaCarpetes } from '../types/Correu';

interface RespostaMissatges {
  missatges: MissatgeLlista[];
}

export async function obtenirCarpetes(): Promise<CarpetaCorreu[]> {
  const resposta = await apiSymfony.get<RespostaCarpetes>(ENDPOINT_CARPETES);

  return resposta.carpetes;
}

export async function obtenirMissatges(carpeta: string, limit = 50, offset = 0): Promise<MissatgeLlista[]> {
  const resposta = await apiSymfony.get<RespostaMissatges>(ENDPOINT_MISSATGES, { carpeta, limit, offset });

  return resposta.missatges;
}

export async function obtenirMissatge(carpeta: string, missatge: MissatgeLlista): Promise<MissatgeDetall> {
  return apiSymfony.get<MissatgeDetall>(ENDPOINT_MISSATGE, {
    carpeta,
    uid: missatge.id,
    uidValidity: missatge.uid_validity,
  });
}

export async function marcarMissatgeLlegit(carpeta: string, missatge: MissatgeLlista): Promise<void> {
  await apiSymfony.patch(ENDPOINT_LLEGIT, {
    carpeta,
    uid: missatge.id,
    uidValidity: missatge.uid_validity,
    llegit: true,
  });
}

export async function enviarMissatge(missatge: MissatgePerEnviar): Promise<void> {
  await apiSymfony.post(ENDPOINT_ENVIAR, missatge);
}

export async function cercarContactes(terme: string): Promise<Contacte[]> {
  const cerca = terme.trim();

  if (cerca.length < 2) {
    return [];
  }

  return api.get<Contacte[]>(ENDPOINT_CERCAR_CONTACTES, { q: cerca });
}
