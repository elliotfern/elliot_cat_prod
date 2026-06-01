export function setWithExpiry(key: string, value: any, ttlSeconds: number) {
  const now = new Date();
  const item = {
    value,
    expiry: now.getTime() + ttlSeconds * 1000, // ttl en milisegundos
  };
  localStorage.setItem(key, JSON.stringify(item));
}

// Leer valor con expiración
export function getWithExpiry(key: string): any | null {
  const itemStr = localStorage.getItem(key);
  if (!itemStr) return null;

  const item = JSON.parse(itemStr);
  const now = new Date();

  if (now.getTime() > item.expiry) {
    localStorage.removeItem(key);
    return null;
  }

  return item.value;
}
