import { api, apiSymfony } from '../../../Infrastructure/Api/Client/ApiClient';
import { ENDPOINT_CARPETES, ENDPOINT_CERCAR_CONTACTES, ENDPOINT_ENVIAR, ENDPOINT_LLEGIT, ENDPOINT_MISSATGE, ENDPOINT_MISSATGES, ENDPOINT_GUARDAR_ESBORRANY, ENDPOINT_LLISTAR_ESBORRANYS, ENDPOINT_ESBORRANY, ENDPOINT_ENVIAR_ESBORRANY } from '../constants/endpoints';
import type { CarpetaCorreu, Contacte, Esborrany, EsborranyPerGuardar, IdentificadorMissatgeCorreu, MissatgeDetall, MissatgeLlista, MissatgePerEnviar, RespostaCarpetes, RespostaGuardarEsborrany, RespostaLlistarEsborranys } from '../types/Correu';

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

/**
 * Guarda un borrador nuevo o actualiza uno existente.
 */
export async function guardarEsborrany(esborrany: EsborranyPerGuardar): Promise<IdentificadorMissatgeCorreu> {
  const resposta = await apiSymfony.post<RespostaGuardarEsborrany>(ENDPOINT_GUARDAR_ESBORRANY, esborrany);

  return resposta.id;
}

/**
 * Recupera todos los borradores.
 */
export async function llistarEsborranys(): Promise<Esborrany[]> {
  const resposta = await apiSymfony.get<RespostaLlistarEsborranys>(ENDPOINT_LLISTAR_ESBORRANYS);

  return resposta.esborranys;
}

/**
 * Recupera un borrador concreto.
 */
export async function obtenirEsborrany(id: IdentificadorMissatgeCorreu): Promise<Esborrany> {
  return apiSymfony.get<Esborrany>(ENDPOINT_ESBORRANY, {
    uid: id.uid,
    uidValidity: id.uid_validity,
  });
}

/**
 * Elimina definitivamente un borrador.
 */
export async function eliminarEsborrany(id: IdentificadorMissatgeCorreu): Promise<void> {
  await apiSymfony.delete<void>(ENDPOINT_ESBORRANY, {
    uid: id.uid,
    uidValidity: id.uid_validity,
  });
}

/**
 * Envía un borrador mediante el flujo SMTP existente.
 */
export async function enviarEsborrany(id: IdentificadorMissatgeCorreu): Promise<void> {
  await apiSymfony.post<void>(ENDPOINT_ENVIAR_ESBORRANY, { id });
}
