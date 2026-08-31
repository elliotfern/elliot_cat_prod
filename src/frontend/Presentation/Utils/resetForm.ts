export function resetForm(formId: string) {
  const form = document.getElementById(formId) as HTMLFormElement | null;
  if (!form) return;

  // 1) Resetea a valores por defecto del HTML
  form.reset();

  // 2) Limpia explícitamente (queremos campos vacíos, no los defaults)
  // Inputs
  form.querySelectorAll<HTMLInputElement>('input').forEach((el) => {
    switch (el.type) {
      case 'checkbox':
      case 'radio':
        el.checked = false;
        break;
      case 'file':
        el.value = '';
        break;
      case 'hidden':
        // lo gestionamos abajo si está ligado a trix; si no, vaciamos
        // (se deja vacío de todas formas)
        el.value = '';
        break;
      default:
        el.value = '';
    }
  });

  // Textareas
  form.querySelectorAll<HTMLTextAreaElement>('textarea').forEach((el) => {
    el.value = '';
  });

  // Selects
  form.querySelectorAll<HTMLSelectElement>('select').forEach((sel) => {
    if (sel.multiple) {
      Array.from(sel.options).forEach((opt) => (opt.selected = false));
    } else {
      // coloca el placeholder si existe (value vacío) o el primer option
      const placeholderIdx = Array.from(sel.options).findIndex((o) => o.value === '');
      sel.selectedIndex = placeholderIdx >= 0 ? placeholderIdx : sel.options.length ? 0 : -1;
    }
    // dispara change para que se actualicen componentes dependientes
    sel.dispatchEvent(new Event('change', { bubbles: true }));
  });

  // 3) Trix editors: vaciar hidden + el editor visual
  form.querySelectorAll<HTMLInputElement>('input[type="hidden"][id]').forEach((hidden) => {
    const editor = form.querySelector(`trix-editor[input="${hidden.id}"]`) as any;
    if (editor?.editor) {
      editor.editor.loadHTML('');
    }
    hidden.value = '';
    // notifica cambios por si tienes listeners
    hidden.dispatchEvent(new Event('input', { bubbles: true }));
    hidden.dispatchEvent(new Event('change', { bubbles: true }));
  });

  // 4) contenteditable (si tuvieras otros)
  form.querySelectorAll<HTMLElement>('[contenteditable]').forEach((el) => {
    el.innerHTML = '';
  });
}
