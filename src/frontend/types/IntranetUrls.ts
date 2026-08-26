export type IntranetUrls = {
  COMPTABILITAT: {
    EMISSOR_MODIFICA_ID: (id: string) => string;
    EMISSOR_FITXA_ID: (id: string) => string;
    CLIENT_MODIFICA_ID: (id: string) => string;
    CLIENT_FITXA_ID: (id: string) => string;
    PROVEIDOR_FITXA_ID: (id: string) => string;
    PROVEIDOR_MODIFICA_ID: (id: string) => string;
    PRESSUPOST_MODIFICA_ID: (id: string) => string;
    FACTURA_MODIFICA_ID: (id: number) => string;
    FACTURA_DESPESA_MODIFICA: (id: string) => string;
    PRODUCTE_MODIFICA: (id: string) => string;
  };

  CONTACTES: {
    CONTACTE_MODIFICA_ID: (id: string) => string;
  };
};
