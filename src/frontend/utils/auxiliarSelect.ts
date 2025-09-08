// src/utils/auxiliarSelect.ts
import Choices from 'choices.js';
import 'choices.js/public/assets/styles/choices.min.css';
import { API_BASE } from './urls';

type Item = { id: number; [key: string]: unknown };
const choicesRegistry = new Map<string, Choices>();

export async function auxiliarSelect(idAux: number | null | undefined, api: string, elementId: string, valorText: string, fallbackValue?: string, config?: Partial<Choices['config']>): Promise<Choices | void> {
  const urlAjax = `${API_BASE}/auxiliars/get/${api}`;

  try {
    const response = await fetch(urlAjax, { method: 'GET', headers: { 'Content-Type': 'application/json' } });
    if (!response.ok) throw new Error('Error en la solicitud');

    const jsonResponse = await response.json();
    const data: Item[] = Array.isArray(jsonResponse?.data) ? jsonResponse.data : jsonResponse;

    const selectElement = document.getElementById(elementId) as HTMLSelectElement | null;
    if (!selectElement) return;

    // destruir instancia anterior
    const prev = choicesRegistry.get(elementId);
    if (prev) {
      prev.destroy();
      choicesRegistry.delete(elementId);
    }

    // limpiar y añadir placeholder (NO disabled, y como primera opción)
    selectElement.innerHTML = '';
    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.text = 'Selecciona una opció:';
    placeholder.setAttribute('selected', '');
    // importante: no disabled / no hidden → se puede volver a "vaciar"
    selectElement.appendChild(placeholder);

    // calcular selección inicial
    const initial = idAux !== null && idAux !== undefined && idAux !== 0 ? String(idAux) : fallbackValue !== undefined ? String(fallbackValue) : '';

    // crear Choices: sin orden alfabético y con botón de eliminar
    const choices = new Choices(selectElement, {
      searchEnabled: true,
      allowHTML: false,
      shouldSort: false, // mantiene el placeholder primero
      placeholder: true,
      placeholderValue: 'Selecciona una opció:',
      removeItemButton: true, // recupera la “x” para limpiar
      itemSelectText: '',
      noResultsText: 'Sense resultats',
      ...config,
    });

    // construir opciones (respetando orden recibido)
    const options = data.map((item) => {
      const raw = item[valorText];
      const label = typeof raw === 'string' ? raw : String(raw ?? '');
      return { value: String(item.id), label };
    });

    // IMPORTANTE: no reemplazar para conservar el placeholder que ya existe
    choices.setChoices(options, 'value', 'label', false);

    // seleccionar inicial si corresponde
    if (initial) {
      choices.setChoiceByValue(initial);
    } else {
      // mantener vacío/placeholder visible
      selectElement.value = '';
      choices.removeActiveItems();
    }

    // al pulsar la “x”, dejar el select en vacío y notificar cambio
    selectElement.addEventListener('removeItem', () => {
      choices.removeActiveItems();
      selectElement.value = '';
      // notifica a listeners (por si guardas/validas en 'change')
      selectElement.dispatchEvent(new Event('change', { bubbles: true }));
    });

    choicesRegistry.set(elementId, choices);
    return choices;
  } catch (error) {
    console.error('Error al cargar las opciones:', error);
  }
}
