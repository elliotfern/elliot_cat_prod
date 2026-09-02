interface StoredItem {
  value: unknown;
  expiry: number;
}

export function setWithExpiry(key: string, value: unknown, ttlSeconds: number): void {
  const item: StoredItem = {
    value,
    expiry: Date.now() + ttlSeconds * 1000,
  };

  localStorage.setItem(key, JSON.stringify(item));
}

export function getWithExpiry(key: string): unknown | null {
  const itemStr = localStorage.getItem(key);

  if (!itemStr) return null;

  const item = JSON.parse(itemStr) as StoredItem;

  if (Date.now() > item.expiry) {
    localStorage.removeItem(key);
    return null;
  }

  return item.value;
}
