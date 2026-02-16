export type LangCode = 'ca' | 'es' | 'en' | 'fr' | 'it';
export const LANGS: LangCode[] = ['ca', 'es', 'en', 'fr', 'it'];
export const ALLOWED_LANGS: LangCode[] = ['ca', 'es', 'en', 'fr', 'it'];

export const LANG_MAP: Record<LangCode, number> = {
  ca: 1,
  en: 2,
  es: 3,
  it: 4,
  fr: 7,
};
