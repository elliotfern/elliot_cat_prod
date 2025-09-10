/** Espera a que exista un trix-editor vinculado a un input concreto y devuelve ese editor */
export async function getTrixEditorForInput(inputId: string, timeoutMs = 2000): Promise<HTMLTrixEditorElement> {
  const start = performance.now();
  while (performance.now() - start < timeoutMs) {
    const editor = document.querySelector(`trix-editor[input="${inputId}"]`) as HTMLTrixEditorElement | null;

    if (editor && editor.editor) return editor;
    await new Promise((r) => setTimeout(r, 50));
  }
  throw new Error(`Trix editor no inicializado para input="${inputId}"`);
}

/** Carga HTML en el editor Trix (y sincroniza el hidden input) de forma robusta */
export async function setTrixHTML(inputId: string, html: string | undefined | null) {
  const safe = html ?? '';
  const hidden = document.getElementById(inputId) as HTMLInputElement | null;
  if (hidden) hidden.value = safe; // asegura el valor en el hidden

  try {
    const editorEl = await getTrixEditorForInput(inputId);

    editorEl.editor.loadHTML(safe);
  } catch {
    // Si no logramos coger el editor a tiempo, al menos queda el hidden con el valor
  }
}
