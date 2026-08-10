export const LOCALES = [
  { id: '019e204b-3047-713a-b67e-f75a39578aea', code: 'ca-ES', label: 'Català', current: 'actualitat' },
  { id: '019e204b-3046-725d-b407-33fee2d9a8f8', code: 'es-ES', label: 'Castellano', current: 'actualidad' },
  { id: '019e204b-3047-713a-b67e-f75a39e4e1e6', code: 'en-US', label: 'English', current: 'current' },
  { id: '019e204b-3046-725d-b407-33fee2e5832b', code: 'it-IT', label: 'Italiano', current: 'attuale' },
] as const;

export type LocaleId = (typeof LOCALES)[number]['id'];
export type Locale = (typeof LOCALES)[number];

export const DEFAULT_LOCALE_ID: LocaleId = LOCALES[0].id; // català

const byId: Map<string, Locale> = new Map(LOCALES.map((l) => [l.id, l]));

export const getLocale = (id: string) => byId.get(id);
export const localeLabel = (id: string) => byId.get(id)?.label ?? `Locale ${id}`;
export const localeCode = (id: string) => byId.get(id)?.code ?? 'ca-ES';
export const localeCurrentLabel = (id: string) => byId.get(id)?.current ?? 'actual';
