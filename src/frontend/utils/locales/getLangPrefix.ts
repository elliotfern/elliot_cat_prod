import { ALLOWED_LANGS, LANG_MAP, LangCode, LANGS } from '../../types/Idioma';
import { DOMAIN_WEB } from '../urls';

export function getLangPrefix(): string {
  const parts = window.location.pathname.split('/').filter(Boolean);
  const first = String(parts[0] ?? '').toLowerCase();
  return (ALLOWED_LANGS as string[]).includes(first) ? `${first}` : 'ca';
}

export function isLang(seg: string | undefined): boolean {
  return LANGS.includes(String(seg ?? '').toLowerCase() as LangCode);
}

export function isInGestio(): boolean {
  const parts = window.location.pathname.split('/').filter(Boolean);
  return parts[0] === 'gestio';
}

/**
 * Construye una URL correcta según contexto:
 * - Admin (gestio) -> /gestio/...
 * - Público        -> /{lang}/...
 *
 * @param path Ej: "/biblioteca/fitxa-autor/slug"
 */
export function buildFrontUrl(path: string): string {
  const cleanPath = path.startsWith('/') ? path : `${path}`;
  const basePrefix = isInGestio() ? 'gestio' : getLangPrefix();
  return `${DOMAIN_WEB}/${basePrefix}/${cleanPath}`;
}

export function getUrlLangCode(): LangCode | null {
  const parts = window.location.pathname.split('/').filter(Boolean);
  const first = String(parts[0] ?? '').toLowerCase();
  if (first in LANG_MAP) return first as LangCode;
  return null;
}

export const LANG_ID_TO_CODE: Record<number, LangCode> = Object.fromEntries(Object.entries(LANG_MAP).map(([code, id]) => [id, code as LangCode])) as Record<number, LangCode>;
