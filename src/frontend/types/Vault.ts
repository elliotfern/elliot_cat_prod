// Definir la interfaz del tipo de dato que esperamos de la API
export interface Vault {
  servei: string;
  usuari: string;
  password: string;
  tipus: string;
  dateModified: string;
  web: string;
  id: number;
  clau2f: string;
}

// Definir una interfaz para los datos que esperamos de la respuesta
export interface TwoFACodeResponse {
  code?: string;
  error?: string;
}
