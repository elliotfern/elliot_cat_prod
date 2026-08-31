export function formDataToObject(form: HTMLFormElement): Record<string, unknown> {
  const formData = new FormData(form);
  const data: Record<string, unknown> = {};

  for (const [key, value] of formData.entries()) {
    const isArray = key.endsWith('[]');
    const cleanKey = isArray ? key.replace('[]', '') : key;

    if (isArray) {
      const arr = (data[cleanKey] as unknown[] | undefined) ?? [];

      arr.push(value);
      data[cleanKey] = arr;
    } else {
      data[cleanKey] = value;
    }
  }

  return data;
}
