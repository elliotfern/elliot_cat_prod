import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { renderFormInputs } from '../../utils/renderInputsForm';

interface Proveidor {
  id?: number;
  nom: string;
  nif?: string;
  adreca?: string;
  ciutat?: string;
  codi_postal?: string;
  pais?: string;
  telefon?: string;
  email?: string;
  web?: string;
  contacte?: string;
  notes?: string;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export async function formProveidor(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formProveidor') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnProveidor') as HTMLButtonElement | null;

  if (!form || !divTitol || !btnSubmit) return;

  let data: Partial<Proveidor> = {};

  // Si es actualización, cargamos los datos
  if (isUpdate && id) {
    const response = await fetchDataGet<ApiResponse<Proveidor>>(API_URLS.GET.PROVEIDOR_ID(id), true);
    if (!response || !response.data) return;

    data = response.data;

    divTitol.innerHTML = `<h2>Modificació Proveïdor</h2>`;
    btnSubmit.textContent = 'Modificar Proveïdor';

    renderFormInputs(data);

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'PUT', 'formProveidor', API_URLS.PUT.PROVEIDOR, true);
    });
  } else {
    // Creación
    divTitol.innerHTML = `<h2>Nou Proveïdor</h2>`;
    btnSubmit.textContent = 'Crear Proveïdor';

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'POST', 'formProveidor', API_URLS.POST.PROVEIDOR, true);
    });
  }
}
