import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

interface SerieTVFitxa {
  status: string;
  message: string;

  id: string;
  name: string;
  slug: string;

  startYear: number;
  endYear: number | null;

  season: number;
  chapter: number;

  director_id: string;
  idioma_id: string;
  genere_id: string;
  pais_id: string;

  img_id: string | null;

  descripcio: string;

  dateCreated: string;
  dateModified: string;

  actors: {
    id: string;
    nom: string;
    cognoms: string;
    slug: string;
    role: string;
  }[];
}

let actorsList: { id: string; nomComplet: string }[] = [];

async function loadActors() {
  try {
    const response = await fetch('https://elliot.cat/api/cinema/get/actors', {
      method: 'GET',
      headers: {
        Accept: 'application/json',
      },
    });

    if (!response.ok) {
      console.error('Error loading actors:', response.status);
      actorsList = [];
      return;
    }

    const data = await response.json();

    actorsList = Array.isArray(data) ? data : (data?.data ?? []);
  } catch (e) {
    console.error('loadActors failed:', e);
    actorsList = [];
  }
}

function createActorSelect(selectedActorId: string | null = null, selectedRole: string = '') {
  const wrapper = document.createElement('div');
  wrapper.className = 'mb-3 p-2 border rounded';

  // =========================
  // LABEL: ACTOR
  // =========================
  const labelActor = document.createElement('label');
  labelActor.className = 'form-label fw-semibold';
  labelActor.textContent = 'Actor';

  const select = document.createElement('select');
  select.name = 'actors[]';
  select.className = 'form-select';

  const empty = document.createElement('option');
  empty.value = '';
  empty.textContent = '-- Selecciona actor --';
  select.appendChild(empty);

  for (const actor of actorsList) {
    const option = document.createElement('option');
    option.value = String(actor.id);
    option.textContent = actor.nomComplet;

    if (selectedActorId && String(selectedActorId) === String(actor.id)) {
      option.selected = true;
    }

    select.appendChild(option);
  }

  // =========================
  // LABEL: ROL
  // =========================
  const labelRole = document.createElement('label');
  labelRole.className = 'form-label fw-semibold mt-2';
  labelRole.textContent = 'Rol';

  const roleInput = document.createElement('input');
  roleInput.type = 'text';
  roleInput.name = 'roles[]';
  roleInput.className = 'form-control';
  roleInput.placeholder = 'Ex: protagonista, secundari...';
  roleInput.value = selectedRole;

  // =========================
  // BOTÓN ELIMINAR
  // =========================
  const removeBtn = document.createElement('button');
  removeBtn.type = 'button';
  removeBtn.className = 'btn btn-sm btn-danger mt-2';
  removeBtn.textContent = 'Eliminar';

  removeBtn.onclick = () => wrapper.remove();

  // =========================
  // APPEND
  // =========================
  wrapper.appendChild(labelActor);
  wrapper.appendChild(select);

  wrapper.appendChild(labelRole);
  wrapper.appendChild(roleInput);

  wrapper.appendChild(removeBtn);

  document.getElementById('actorsContainer')?.appendChild(wrapper);
}

function initActorUI() {
  const btn = document.getElementById('addActorBtn');

  btn?.addEventListener('click', () => {
    createActorSelect();
  });
}

export async function formSerie(isUpdate: boolean, idUuid?: string) {
  let data: Partial<SerieTVFitxa> = { id: '' };

  const form = document.getElementById('formSerie');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnForm') as HTMLButtonElement;

  if (!divTitol || !btnSubmit || !form) return;

  const actorsPromise = loadActors();

  await actorsPromise;

  if (idUuid && isUpdate) {
    const response = await fetch(`https://elliot.cat/api/cinema/get/serieIntranet?id=${idUuid}`);

    const responseData = await response.json();

    if (!responseData || !responseData.data) return;

    data = responseData.data;

    renderFormInputs(data);

    if (!response || !data) return;

    divTitol.innerHTML = `<h2>Modificació dades Sèrie tv</h2>`;

    const fileInput = document.getElementById('img_upload') as HTMLInputElement;
    if (fileInput) fileInput.value = '';

    const container = document.getElementById('actorsContainer');
    if (container) container.innerHTML = '';

    initActorUI();

    if (data?.actors?.length) {
      for (const actor of data.actors) {
        createActorSelect(String(actor.id), actor.role);
      }
    } else {
      createActorSelect();
    }

    btnSubmit.textContent = 'Modificar dades';
    const id = (data.id ?? '').toString();

    if (!id) {
      console.error('ID de persona no disponible');
      return;
    }

    form.addEventListener('submit', function (event) {
      // Lo mandamos por POST porque PUT no funciona bien con ficheros
      transmissioDadesDB(event, 'POST', 'formSerie', `https://elliot.cat/api/cinema/put/serie`);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nova sèrie tv</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    const container = document.getElementById('actorsContainer');
    if (container) container.innerHTML = '';

    initActorUI();

    // crear primer select vacío
    createActorSelect();

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formSerie', 'https://elliot.cat/api/cinema/post/serie', true);
    });
  }

  await auxiliarSelect(data.director_id ?? '', 'directors', 'director_id', 'nomComplet');
  await auxiliarSelect(data.img_id ?? '', 'auxiliarImatgesSeries', 'img_id', 'alt');
  await auxiliarSelect(data.genere_id ?? '', 'generesPelis', 'genere_id', 'genere');
  await auxiliarSelect(data.idioma_id ?? '', 'llengues', 'idioma_id', 'idioma_ca');
  await auxiliarSelect(data.pais_id ?? '', 'paisos', 'pais_id', 'pais_ca');
}
