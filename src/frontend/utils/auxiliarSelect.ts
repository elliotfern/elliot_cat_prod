// src/utils/auxiliarSelect.ts
import Choices from 'choices.js';
import 'choices.js/public/assets/styles/choices.min.css';
import { API_BASE } from './urls';

type Item = { id: number | string; [key: string]: unknown };
const choicesRegistry = new Map<string, Choices>();

export async function auxiliarSelect(selected: number | string | Array<number | string> | null | undefined, api: string, elementId: string, valorText: string, fallbackValue?: string, config?: Partial<Choices['config']>): Promise<Choices | void> {
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

    // limpiar y añadir placeholder
    selectElement.innerHTML = '';
    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.text = 'Selecciona una opció:';
    placeholder.setAttribute('selected', '');
    selectElement.appendChild(placeholder);

    // normalizar valores seleccionados a array de strings
    let selectedValues: string[] = [];
    if (Array.isArray(selected)) {
      selectedValues = selected.map((v) => String(v));
    } else if (selected !== null && selected !== undefined && selected !== 0) {
      selectedValues = [String(selected)];
    } else if (fallbackValue !== undefined) {
      selectedValues = [String(fallbackValue)];
    }

    // inicializar Choices
    const choices = new Choices(selectElement, {
      searchEnabled: true,
      allowHTML: false,
      shouldSort: false,
      placeholder: true,
      placeholderValue: 'Selecciona una opció:',
      removeItemButton: true,
      itemSelectText: '',
      noResultsText: 'Sense resultats',
      ...config,
    });

    // construir opciones
    const options = data.map((item) => {
      const raw = item[valorText];
      const label = typeof raw === 'string' ? raw : String(raw ?? '');
      return { value: String(item.id), label };
    });

    choices.setChoices(options, 'value', 'label', false);

    // aplicar selección inicial
    if (selectedValues.length > 0) {
      choices.setChoiceByValue(selectedValues);
    } else {
      selectElement.value = '';
      choices.removeActiveItems();
    }

    // manejar “x” → limpiar select
    selectElement.addEventListener('removeItem', () => {
      choices.removeActiveItems();
      selectElement.value = '';
      selectElement.dispatchEvent(new Event('change', { bubbles: true }));
    });

    choicesRegistry.set(elementId, choices);
    return choices;
  } catch (error) {
    console.error('Error al cargar las opciones:', error);
  }
}
