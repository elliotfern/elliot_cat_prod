interface TrixEditorElement extends HTMLElement {
  editor: {
    loadHTML: (html: string) => void;
  };
}

/**
 * Funció per omplir els inputs text i select de les pàgines de formularis de modificació.
 * @param url - L'URL de l'API per obtenir les dades.
 * @param id - L'ID de l'element a obtenir.
 * @param formId - L'ID del formulari HTML que s'omplirà.
 * @param callback - La funció de callback que es cridarà amb les dades obtingudes.
 */
export async function omplirDadesForm(url: string, id: number, formId: string, callback: (data: any) => void): Promise<void> {
  const urlAjax = `${url}${id}`;

  try {
    const response = await fetch(urlAjax, {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${localStorage.getItem('token') || ''}`,
      },
    });

    if (!response.ok) {
      throw new Error('Error en la sol·licitud AJAX');
    }

    const data = await response.json();
    callback(data);

    // Omplir el formulari amb les dades obtingudes
    const form = document.getElementById(formId) as HTMLFormElement;
    if (!form) {
      console.error(`Form with id ${formId} not found`);
      return;
    }

    Object.keys(data).forEach((key) => {
      const input = form.querySelector(`[name="${key}"]`) as HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement;
      if (input) {
        input.value = data[key];
      }
    });

    // Verificar si data.descripcio existe y no está vacío
    if (data['descripcio']) {
      initializeQuill('descripcio', data['descripcio']);
    }
  } catch (error) {
    console.error('Error:', error);
  }
}

function initializeQuill(textareaId: string, content: string | null) {
  const textarea = document.getElementById(textareaId) as HTMLTextAreaElement;

  if (!textarea) {
    console.error(`No se encontró el textarea con id ${textareaId}`);
    return;
  }

  // Crear el contenedor del editor si no existe
  let editorContainer = document.getElementById('quill-editor');
  if (!editorContainer) {
    editorContainer = document.createElement('div');
    editorContainer.id = 'quill-editor';
    textarea.insertAdjacentElement('afterend', editorContainer);
    textarea.style.display = 'none'; // Ocultar el textarea original
  }
}
