import { api } from '../../core/api/client';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';
import { API_BASE } from '../../utils/urls';

type Medicament = {
  id: string;
  medicament: string;
};

type Patologia = {
  id: string;
  patologia: string;
  genere: string;
  medicaments?: { id: string; medicaments: string }[];
};

let medicamentsList: Medicament[] = [];

async function loadMedicaments() {
  try {
    medicamentsList = await api.get<Medicament[]>(`salut/get/llistatMedicaments`);
  } catch (error) {
    console.error('loadMedicaments failed:', error);

    medicamentsList = [];
  }
}

function createMedicamentSelect(selectedValue: string | null = null) {
  const wrapper = document.createElement('div');
  wrapper.className = 'd-flex gap-2 mb-2';

  const select = document.createElement('select');
  select.name = 'medicaments[]';
  select.className = 'form-select';

  const empty = document.createElement('option');
  empty.value = '';
  empty.textContent = '-- Selecciona medicament --';
  select.appendChild(empty);

  for (const medicament of medicamentsList) {
    const option = document.createElement('option');

    option.value = String(medicament.id);
    option.textContent = medicament.medicament;

    if (selectedValue && String(selectedValue) === String(medicament.id)) {
      option.selected = true;
    }

    select.appendChild(option);
  }

  const removeBtn = document.createElement('button');
  removeBtn.type = 'button';
  removeBtn.className = 'btn btn-danger';
  removeBtn.textContent = '✕';

  removeBtn.onclick = () => wrapper.remove();

  wrapper.appendChild(select);
  wrapper.appendChild(removeBtn);

  const container = document.getElementById('medicamentsContainer');
  container?.appendChild(wrapper);
}

function initMedicamentUI() {
  const btn = document.getElementById('addMedicamentBtn');

  btn?.addEventListener('click', () => {
    createMedicamentSelect();
  });
}

export async function formPatologia(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formPatologia');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnPatologia') as HTMLButtonElement;

  if (!divTitol || !btnSubmit || !form) return;

  await loadMedicaments();

  let data: Partial<Patologia> = {};

  if (id && isUpdate) {
    try {
      const response = await fetch(`${API_BASE}/salut/get/patologiaID?id=${id}`);
      const responseData = await response.json();

      if (!responseData || !responseData.data) return;

      data = responseData.data;
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació dades patologia</h2>`;

    renderFormInputs(data);

    const medicamentsContainer = document.getElementById('medicamentsContainer');
    if (medicamentsContainer) medicamentsContainer.innerHTML = '';

    initMedicamentUI();

    if (data?.medicaments?.length) {
      for (const medicament of data.medicaments) {
        createMedicamentSelect(String(medicament.id));
      }
    } else {
      createMedicamentSelect();
    }

    btnSubmit.textContent = 'Modificar dades';

    if (!data.id) {
      console.error('ID de patologia no disponible');
      return;
    }

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formPatologia', `${API_BASE}/salut/put/patologia`, true);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nova patologia</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    const medicamentsContainer = document.getElementById('medicamentsContainer');
    if (medicamentsContainer) medicamentsContainer.innerHTML = '';

    initMedicamentUI();
    createMedicamentSelect();

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formPatologia', `${API_BASE}/salut/post/patologia`, true);
    });
  }
}
