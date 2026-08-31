import Choices from 'choices.js';
import 'choices.js/public/assets/styles/choices.min.css';

export interface AuxiliarSelectItem {
  id: string | number;
}

const choicesRegistry = new Map<string, Choices>();

const ZERO_UUID = /^0{8}-0{4}-0{4}-0{4}-0{12}$/i;

const isEmptySel = (value: unknown): boolean => value === null || value === undefined || value === '' || value === 0 || value === '0' || (typeof value === 'string' && ZERO_UUID.test(value));

export async function auxiliarSelectData<T extends AuxiliarSelectItem>(selected: string | number | Array<string | number> | null | undefined, data: T[], elementId: string, valorText: keyof T): Promise<Choices | void> {
  const selectElement = document.getElementById(elementId) as HTMLSelectElement | null;

  if (!selectElement) {
    return;
  }

  // Destruir Choices anterior
  const previous = choicesRegistry.get(elementId);

  if (previous) {
    previous.destroy();
    choicesRegistry.delete(elementId);
  }

  // Limpiar select
  selectElement.innerHTML = '';

  // Placeholder
  const placeholder = document.createElement('option');

  placeholder.value = '';
  placeholder.text = 'Selecciona una opció:';

  selectElement.appendChild(placeholder);

  // Normalizar selección
  let selectedValues: string[] = [];

  if (Array.isArray(selected)) {
    selectedValues = selected.map(String).filter((value) => !isEmptySel(value));
  } else if (!isEmptySel(selected)) {
    selectedValues = [String(selected)];
  }

  // Choices
  const choices = new Choices(selectElement, {
    searchEnabled: true,
    allowHTML: false,
    shouldSort: false,
    placeholder: true,
    placeholderValue: 'Selecciona una opció:',
    removeItemButton: !!selectElement.multiple,
    itemSelectText: '',
    noResultsText: 'Sense resultats',
  });

  // Opciones
  const options = data.map((item) => ({
    value: String(item.id),
    label: String(item[valorText] ?? ''),
  }));

  choices.setChoices(options, 'value', 'label', false);

  // Preselección
  if (selectedValues.length > 0) {
    choices.setChoiceByValue(selectedValues);
  } else {
    selectElement.value = '';
    choices.removeActiveItems();
  }

  choicesRegistry.set(elementId, choices);

  return choices;
}
