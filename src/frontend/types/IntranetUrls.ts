export type IntranetUrls = {
  COMPTABILITAT: {
    EMISSOR_MODIFICA_ID: (id: string) => string;
    EMISSOR_FITXA_ID: (id: string) => string;
    CLIENT_MODIFICA_ID: (id: string) => string;
    CLIENT_FITXA_ID: (id: string) => string;
    PROVEIDOR_FITXA_ID: (id: string) => string;
    PROVEIDOR_MODIFICA_ID: (id: string) => string;
  };
};
