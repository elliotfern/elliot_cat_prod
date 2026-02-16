import { ALLOWED_LANGS, LangCode } from '../../types/Idioma';

export function getLangPrefix(): string {
  const parts = window.location.pathname.split('/').filter(Boolean);
  const first = String(parts[0] ?? '').toLowerCase();
  return (ALLOWED_LANGS as string[]).includes(first) ? `${first}` : 'ca';
}

export const LANGS: LangCode[] = ['ca', 'es', 'en', 'fr', 'it'];

export function isLang(seg: string | undefined): boolean {
  return LANGS.includes(String(seg ?? '').toLowerCase() as LangCode);
}

export function isInGestio(): boolean {
  const parts = window.location.pathname.split('/').filter(Boolean);
  return parts[0] === 'gestio';
}
