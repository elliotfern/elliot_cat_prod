export type ApiUrls = {
  GET: {
    PERFIL_CV_ID: (id: number) => string;
    PERFIL_CV_I18N_ID: (id: number, locale: number) => string;
    LINK_CV_ID: (id: number) => string;
  };
  POST: {
    PERFIL_CV: string;
    PERFIL_CV_I18N: string;
    LINK_CV: string;
  };
  PUT: {
    PERFIL_CV: string;
    PERFIL_CV_I18N: string;
    LINK_CV: string;
  };
  DELETE: {};
};
