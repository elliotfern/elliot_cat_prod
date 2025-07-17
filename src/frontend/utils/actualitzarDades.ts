// AJAX PROCESS > PHP API : PER ACTUALIZAR FORMULARIS A LA BD
export async function transmissioDadesDB(event: Event, tipus: string, formId: string, urlAjax: string): Promise<void> {
  event.preventDefault();

  const form = document.getElementById(formId) as HTMLFormElement;
  if (!form) {
    console.error(`Form with id ${formId} not found`);
    return;
  }

  // Detectar todos los campos con data-type="number"
  const numericFields = new Set<string>();
  form.querySelectorAll('[data-type="number"]').forEach((el) => {
    if (el instanceof HTMLInputElement || el instanceof HTMLSelectElement || el instanceof HTMLTextAreaElement) {
      if (el.name) numericFields.add(el.name);
    }
  });

  const formDataRaw = new FormData(form);
  const formData: { [key: string]: FormDataEntryValue | FormDataEntryValue[] | number } = {};

  formDataRaw.forEach((value, key) => {
    const cleanKey = key.endsWith('[]') ? key.slice(0, -2) : key;

    let processedValue: FormDataEntryValue | number = value;
    if (numericFields.has(cleanKey)) {
      const n = parseInt(value.toString(), 10);
      processedValue = isNaN(n) ? value : n;
    }

    if (formData[cleanKey]) {
      if (Array.isArray(formData[cleanKey])) {
        (formData[cleanKey] as FormDataEntryValue[]).push(processedValue as FormDataEntryValue);
      } else {
        formData[cleanKey] = [formData[cleanKey] as FormDataEntryValue, processedValue as FormDataEntryValue];
      }
    } else {
      formData[cleanKey] = processedValue;
    }
  });

  const jsonData = JSON.stringify(formData);

  try {
    const response = await fetch(urlAjax, {
      method: tipus,
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json', // Añadir Content-Type aquí
      },
      body: jsonData,
    });

    const data = await response.json();

    const missatgeOk = document.getElementById('missatgeOk');
    const missatgeErr = document.getElementById('missatgeErr');

    if (data.status === 'success') {
      if (missatgeOk && missatgeErr) {
        missatgeOk.style.display = 'block';
        missatgeErr.style.display = 'none';
        missatgeOk.textContent = "L'operació s'ha realizat correctament a la base de dades.";
        limpiarFormulario(formId);
        setTimeout(() => {
          missatgeOk.style.display = 'none';
        }, 5000);
      }
    } else {
      if (missatgeOk && missatgeErr) {
        missatgeErr.style.display = 'block';
        missatgeOk.style.display = 'none';
        missatgeErr.textContent = "L'operació no s'ha pogut realizar correctament a la base de dades.";
      }
    }
  } catch (error) {
    const missatgeOk = document.getElementById('missatgeOk');
    const missatgeErr = document.getElementById('missatgeErr');
    if (missatgeOk && missatgeErr) {
      console.error('Error:', error);
      missatgeErr.style.display = 'block';
      missatgeOk.style.display = 'none';
    }
  }
}

function limpiarFormulario(formId: string) {
  const formulario = document.getElementById(formId) as HTMLFormElement;
  const inputs = formulario.querySelectorAll('input, textarea, select, trix-editor');

  inputs.forEach((input) => {
    if (input instanceof HTMLInputElement || input instanceof HTMLTextAreaElement) {
      input.value = ''; // Limpiar el valor del campo
    }
    if (input instanceof HTMLSelectElement) {
      input.selectedIndex = 0; // Limpiar el select (poner el primer valor por defecto)
    }
    if (input instanceof HTMLElement && input.tagName === 'TRIX-EDITOR') {
      // Limpiar el editor Trix (Type Assertion)
      const trixEditor = input as HTMLTrixEditorElement;
      trixEditor.editor.loadHTML(''); // Limpiar el contenido del editor Trix
    }
  });
}

// Declara el tipo extendido para TrixEditor
interface HTMLTrixEditorElement extends HTMLElement {
  editor: {
    loadHTML: (html: string) => void;
  };
}
