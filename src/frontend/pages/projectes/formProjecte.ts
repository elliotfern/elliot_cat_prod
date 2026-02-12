import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

interface FitxaProjecte {
  [key: string]: unknown;

  // DB
  id: number;
  name: string;
  description: string | null;

  status: number; // tinyint unsigned (default 1)
  category_id: number | null;

  start_date: string | null; // YYYY-MM-DD
  end_date: string | null; // YYYY-MM-DD

  priority: number; // tinyint unsigned (default 3)

  client_id: number | null;
  budget_id: number | null;
  invoice_id: number | null;

  // Meta (no editable, pero puede venir en el GET)
  created_at?: string;
  updated_at?: string;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export async function formProjecte(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formProjecte') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnProjecte') as HTMLButtonElement | null;

  if (!divTitol || !btnSubmit || !form) return;

  // Defaults según DB (status=1, priority=3)
  let data: Partial<FitxaProjecte> = {
    status: 1,
    priority: 3,
    category_id: null,
    client_id: null,
    budget_id: null,
    invoice_id: null,
    start_date: null,
    end_date: null,
    description: null,
  };

  if (id && isUpdate) {
    const response = await fetchDataGet<ApiResponse<FitxaProjecte>>(API_URLS.GET.PROJECTE_ID(id), true);
    if (!response || !response.data) return;

    data = response.data;

    divTitol.innerHTML = `<h2>Modificació del projecte</h2>`;
    btnSubmit.textContent = 'Modificar dades';

    // Rellena inputs por name/id
    renderFormInputs(data);

    // Submit PUT
    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formProjecte', API_URLS.PUT.PROJECTE);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou projecte</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    // Submit POST
    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formProjecte', API_URLS.POST.PROJECTE, true);
    });
  }

  // Selects (precarga con el valor actual)
  // OJO: auxiliarSelect(valorSeleccionado, taula/slug, selectId/name, labelKey)
  await auxiliarSelect(data.category_id ?? 0, 'projectes_categories', 'category_id', 'name');

  await auxiliarSelect(data.client_id ?? 0, 'clients', 'client_id', 'clientEmpresa');
  await auxiliarSelect(data.budget_id ?? 0, 'budgets', 'budget_id', 'concepte');
  await auxiliarSelect(data.invoice_id ?? 0, 'facturesClients', 'invoice_id', 'facConcepte');
}
