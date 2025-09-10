export type ApiUrls = {
  GET: {
    PERFIL_CV_ID: (id: number) => string;
    PERFIL_CV_I18N_ID: (id: number, locale: number) => string;
    LINK_CV_ID: (id: number) => string;
    LINKS_CV: string;
    HABILITAT_ID: (id: number) => string;
    HABILITATS: string;
    EXPERIENCIA_ID: (id: number) => string;
    EXPERIENCIES: string;
    EXPERIENCIA_I18N_ID: (id: number) => string;
    EXPERIENCIA_I18N_DETALL_ID: (id: number) => string;
  };
  POST: {
    PERFIL_CV: string;
    PERFIL_CV_I18N: string;
    LINK_CV: string;
    HABILITAT: string;
    EXPERIENCIA: string;
    EXPERIENCIA_I18N: string;
  };
  PUT: {
    PERFIL_CV: string;
    PERFIL_CV_I18N: string;
    LINK_CV: string;
    HABILITAT: string;
    EXPERIENCIA: string;
    EXPERIENCIA_I18N: string;
  };
  DELETE: {};
};
