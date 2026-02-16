type LangCode = 'ca' | 'es' | 'en' | 'fr' | 'it';

const SUPPORTED_LANGS: LangCode[] = ['ca', 'es', 'en', 'fr', 'it'];

function getCurrentLang(): LangCode {
  const parts = window.location.pathname.split('/').filter(Boolean);
  const first = String(parts[0] ?? '').toLowerCase();
  return (SUPPORTED_LANGS as string[]).includes(first) ? (first as LangCode) : 'ca';
}

function prefixPathWithLang(path: string, lang: LangCode): string {
  // path esperado tipo "/blog" o "blog"
  const raw = path.startsWith('/') ? path : `/${path}`;
  const parts = raw.split('/').filter(Boolean);

  // si por error viene ya con idioma, lo quitamos
  const first = String(parts[0] ?? '').toLowerCase();
  const rest = (SUPPORTED_LANGS as string[]).includes(first) ? parts.slice(1) : parts;

  return '/' + [lang, ...rest].join('/');
}

function swapLangInCurrentPath(targetLang: LangCode): string {
  const parts = window.location.pathname.split('/').filter(Boolean);
  if (!parts.length) return `/${targetLang}/homepage`;

  const first = String(parts[0] ?? '').toLowerCase();
  if ((SUPPORTED_LANGS as string[]).includes(first)) {
    parts[0] = targetLang;
    return '/' + parts.join('/');
  }

  // si estás en una ruta sin idioma (por cualquier motivo), lo añadimos
  return '/' + [targetLang, ...parts].join('/');
}

export function initI18nHeaderLinks(): void {
  const lang = getCurrentLang();

  // Links del menú que son rutas base (data-route="/historia", etc.)
  document.querySelectorAll<HTMLAnchorElement>('a[data-route]').forEach((a) => {
    const route = a.getAttribute('data-route') || '';
    if (!route) return;
    a.href = prefixPathWithLang(route, lang);
  });

  // Dropdown idiomas: misma página, cambiando prefijo
  document.querySelectorAll<HTMLAnchorElement>('a[data-lang]').forEach((a) => {
    const target = String(a.getAttribute('data-lang') || '').toLowerCase();
    if (!(SUPPORTED_LANGS as string[]).includes(target)) return;
    a.href = swapLangInCurrentPath(target as LangCode);
  });

  // etiqueta del dropdown (opcional)
  const dd = document.getElementById('languagesDropdown');
  if (dd) dd.textContent = lang.toUpperCase();
}
