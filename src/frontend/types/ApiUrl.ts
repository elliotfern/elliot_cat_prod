export type ApiUrls = {
  GET: {
    PERFIL_CV_ID: (id: number) => string;
    PERFIL_CV_I18N_ID: (id: number, locale: number) => string;
  };
  POST: {
    PERFIL_CV: string;
    PERFIL_CV_I18N: string;
  };
  PUT: {
    PERFIL_CV: string;
    PERFIL_CV_I18N: string;
  };
  DELETE: {};
};
