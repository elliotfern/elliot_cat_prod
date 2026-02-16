import { ALLOWED_LANGS } from '../../types/Idioma';

export function getLangPrefix(): string {
  const parts = window.location.pathname.split('/').filter(Boolean);
  const first = String(parts[0] ?? '').toLowerCase();
  return (ALLOWED_LANGS as string[]).includes(first) ? `${first}` : 'ca';
}
