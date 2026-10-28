import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../Presentation/Utils/renderInputsForm';
import Choices from 'choices.js';
import { setTrixHTML } from '../../utils/setTrix';

interface PersonaEsdeveniment {
  id: string;
  nom: string;
  cognoms: string;
  slug: string;
}

interface OrganitzacioEsdeveniment {
  id: string;
  organitzacio: string;
  sigla: string | null;
  slug: string;
}

interface EsdevenimentHistoria {
  id?: string;
  esdeNom?: string;
  slug?: string;
  esdeDataIDia?: number | string;
  esdeDataIMes?: number | string;
  esdeDataIAny?: number | string;
  esdeDataFDia?: number | string;
  esdeDataFMes?: number | string;
  esdeDataFAny?: number | string;
  sub_etapa_id?: string | number;
  ciutat_id?: string | number;
  imatge_id?: string | number;
  dateCreated?: string;
  dateModified?: string;
  nomSubEtapa?: string | null;
  etapaNom?: string | null;
  ciutat?: string | null;
  pais?: string | null;
  nameImg?: string | null;
  alt?: string | null;
  persones?: PersonaEsdeveniment[];
  organitzacions?: OrganitzacioEsdeveniment[];
  [key: string]: unknown;
}

let personesList: PersonaEsdeveniment[] = [];
let organitzacionsList: OrganitzacioEsdeveniment[] = [];

/**
 * Crea un selector dinàmic de persones.
 */
function createPersonaSelect(selectedId: string | null = null): void {
  const container = document.getElementById('personesContainer');

  if (!container) {
    console.error("No s'ha trobat personesContainer.");
    return;
  }

  const wrapper = document.createElement('div');
  wrapper.className = 'd-flex gap-2 mb-2';
  wrapper.style.width = '100%';

  const select = document.createElement('select');
  select.name = 'persones[]';
  select.className = 'form-select';
  select.setAttribute('aria-label', 'Persona relacionada');
  select.style.flex = '1';
  select.style.minWidth = '0';

  const emptyOption = document.createElement('option');
  emptyOption.value = '';
  emptyOption.textContent = '-- Selecciona una persona --';
  select.appendChild(emptyOption);

  for (const persona of personesList) {
    const option = document.createElement('option');

    option.value = persona.id;
    option.textContent = `${persona.nom} ${persona.cognoms}`.trim();

    if (selectedId === persona.id) {
      option.selected = true;
    }

    select.appendChild(option);
  }

  const removeButton = document.createElement('button');
  removeButton.type = 'button';
  removeButton.className = 'btn btn-outline-danger';
  removeButton.textContent = '✕';
  removeButton.setAttribute('aria-label', 'Eliminar persona relacionada');

  wrapper.append(select, removeButton);
  container.appendChild(wrapper);

  const choices = new Choices(select, {
    searchEnabled: true,
    allowHTML: false,
    shouldSort: false,
    placeholder: true,
    placeholderValue: 'Selecciona una persona:',
    itemSelectText: '',
    noResultsText: 'Sense resultats',
  });

  if (selectedId !== null) {
    const personaExiste = personesList.some((persona) => persona.id === selectedId);

    if (personaExiste) {
      choices.setChoiceByValue(selectedId);
    } else {
      console.warn('No s’ha trobat la persona relacionada:', selectedId);
    }
  }

  const choicesContainer = wrapper.querySelector<HTMLElement>('.choices');

  if (choicesContainer) {
    choicesContainer.style.flex = '1';
    choicesContainer.style.minWidth = '0';
  }

  removeButton.addEventListener('click', () => {
    choices.destroy();
    wrapper.remove();
  });
}

/**
 * Crea un selector dinàmic d'organitzacions.
 */
function createOrganitzacioSelect(selectedId: string | null = null): void {
  const container = document.getElementById('organitzacionsContainer');

  if (!container) {
    console.error("No s'ha trobat organitzacionsContainer.");
    return;
  }

  const wrapper = document.createElement('div');
  wrapper.className = 'd-flex gap-2 mb-2';
  wrapper.style.width = '100%';

  const select = document.createElement('select');
  select.name = 'organitzacions[]';
  select.className = 'form-select';
  select.setAttribute('aria-label', 'Organització relacionada');
  select.style.flex = '1';
  select.style.minWidth = '0';

  const emptyOption = document.createElement('option');
  emptyOption.value = '';
  emptyOption.textContent = '-- Selecciona una organització --';
  select.appendChild(emptyOption);

  for (const organitzacio of organitzacionsList) {
    const option = document.createElement('option');

    option.value = organitzacio.id;
    option.textContent = organitzacio.sigla ? `${organitzacio.organitzacio} (${organitzacio.sigla})` : organitzacio.organitzacio;

    if (selectedId === organitzacio.id) {
      option.selected = true;
    }

    select.appendChild(option);
  }

  const removeButton = document.createElement('button');
  removeButton.type = 'button';
  removeButton.className = 'btn btn-outline-danger';
  removeButton.textContent = '✕';
  removeButton.setAttribute('aria-label', 'Eliminar organització relacionada');

  wrapper.append(select, removeButton);
  container.appendChild(wrapper);

  const choices = new Choices(select, {
    searchEnabled: true,
    allowHTML: false,
    shouldSort: false,
    placeholder: true,
    placeholderValue: 'Selecciona una organització:',
    itemSelectText: '',
    noResultsText: 'Sense resultats',
  });

  if (selectedId !== null) {
    const organitzacioExisteix = organitzacionsList.some((organitzacio) => organitzacio.id === selectedId);

    if (organitzacioExisteix) {
      choices.setChoiceByValue(selectedId);
    } else {
      console.warn('No s’ha trobat l’organització relacionada:', selectedId);
    }
  }

  const choicesContainer = wrapper.querySelector<HTMLElement>('.choices');

  if (choicesContainer) {
    choicesContainer.style.flex = '1';
    choicesContainer.style.minWidth = '0';
  }

  removeButton.addEventListener('click', () => {
    choices.destroy();
    wrapper.remove();
  });
}

/**
 * Inicialitza els botons per afegir relacions.
 */
function initRelacionsUI(): void {
  const addPersonaButton = document.getElementById('addPersonaBtn');
  const addOrganitzacioButton = document.getElementById('addOrganitzacioBtn');

  if (addPersonaButton) {
    addPersonaButton.onclick = () => createPersonaSelect();
  }

  if (addOrganitzacioButton) {
    addOrganitzacioButton.onclick = () => createOrganitzacioSelect();
  }
}

/**
 * Carrega els llistats necessaris per al formulari.
 */
async function carregarRelacions(): Promise<void> {
  const [persones, organitzacions] = await Promise.all([api.get<PersonaEsdeveniment[]>('persones/get/llistatPersones'), api.get<OrganitzacioEsdeveniment[]>('historia/get/llistatOrganitzacions')]);

  personesList = persones;
  organitzacionsList = organitzacions;
}

/**
 * Mostra les relacions existents d'un esdeveniment.
 */
function mostrarRelacions(data: EsdevenimentHistoria): void {
  const personesContainer = document.getElementById('personesContainer');
  const organitzacionsContainer = document.getElementById('organitzacionsContainer');

  personesContainer?.replaceChildren();
  organitzacionsContainer?.replaceChildren();

  for (const persona of data.persones ?? []) {
    createPersonaSelect(persona.id);
  }

  for (const organitzacio of data.organitzacions ?? []) {
    createOrganitzacioSelect(organitzacio.id);
  }
}

/**
 * Formulari de creació i modificació d'esdeveniments històrics.
 */
export async function formEsdevenimentHistoria(isUpdate: boolean, id?: string): Promise<void> {
  const form = document.getElementById('formEsdeveniment');
  const divTitol = document.getElementById('titolForm');
  const btnSubmit = document.getElementById('btnEsde');

  if (!(form instanceof HTMLFormElement) || !(divTitol instanceof HTMLDivElement) || !(btnSubmit instanceof HTMLButtonElement)) {
    console.error("No s'han trobat els elements del formulari.");
    return;
  }

  let data: EsdevenimentHistoria = {};

  try {
    await carregarRelacions();

    if (isUpdate) {
      if (!id) {
        console.error("Cal indicar l'identificador de l'esdeveniment.");
        return;
      }

      data = await api.get<EsdevenimentHistoria>(API_URLS.GET.ESDEVENIMENT_ID(id));

      divTitol.innerHTML = '<h2>Modificació esdeveniment</h2>';

      // Renderitza els camps del formulari, sense les relacions.
      const formData: Record<string, unknown> = { ...data };

      delete formData.persones;
      delete formData.organitzacions;

      renderFormInputs(formData);

      // Trix
      setTrixHTML('descripcio', String(data.descripcio ?? ''));

      btnSubmit.textContent = 'Modificar dades';
    } else {
      divTitol.innerHTML = '<h2>Creació de nou esdeveniment</h2>';

      btnSubmit.textContent = 'Inserir dades';
    }

    // Carrega els selectors auxiliars existents.
    await auxiliarSelect(data.ciutat_id ?? 0, 'ciutats', 'ciutat_id', 'ciutat');
    await auxiliarSelect(data.sub_etapa_id ?? 0, 'subEtapesHistoriques', 'sub_etapa_id', 'nomSubEtapa');
    await auxiliarSelect(data.imatge_id ?? 0, 'imgEsde', 'imatge_id', 'alt');
    await auxiliarSelect(data.esdeDataIDia ?? 0, 'calendariDies', 'esdeDataIDia', 'dia');
    await auxiliarSelect(data.esdeDataFDia ?? 0, 'calendariDies', 'esdeDataFDia', 'dia');
    await auxiliarSelect(data.esdeDataIMes ?? 0, 'calendariMesos', 'esdeDataIMes', 'mes');
    await auxiliarSelect(data.esdeDataFMes ?? 0, 'calendariMesos', 'esdeDataFMes', 'mes');
    await auxiliarSelect(data.imatge_id ?? 0, 'imgEsde', 'imatge_id', 'alt');

    // Inicialitza les relacions dinàmiques.
    initRelacionsUI();
    mostrarRelacions(data);

    // Evita registrar múltiples listeners en reinicialitzar el formulari.
    form.onsubmit = (event: SubmitEvent) => {
      if (isUpdate) {
        void transmissioDadesDB(event, 'PUT', 'formEsdeveniment', API_URLS.PUT.ESDEVENIMENT_HISTORIA);
      } else {
        void transmissioDadesDB(event, 'POST', 'formEsdeveniment', API_URLS.POST.ESDEVENIMENT_HISTORIA, true);
      }
    };
  } catch (error: unknown) {
    console.error("Error en carregar el formulari de l'esdeveniment:", error);
  }
}
