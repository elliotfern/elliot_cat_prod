import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

interface Fitxa {
  [key: string]: unknown;
  status: string;
  message: string;
  id: number;
  factura_id: number;
  producte_id: number;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export async function formFacturaProducte(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formFacturaProducte');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnFacturaProducte') as HTMLButtonElement;

  let data: Partial<Fitxa> = {
    comarca: 0,
    provincia: 0,
    comunitat: 0,
    estat: 0,
  };

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    const response = await fetchDataGet<ApiResponse<Fitxa>>(API_URLS.GET.FACTURA_CLIENT_PRODUCTE_ID(id), true);

    if (!response || !response.data) return;
    data = response.data;

    divTitol.innerHTML = `<h2>Modificació producte a la Factura client</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formFacturaProducte', API_URLS.PUT.FACTURA_CLIENT_PRODUCTE);
    });
  } else {
    divTitol.innerHTML = `<h2>Afegir producte a la factura</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formFacturaProducte', API_URLS.POST.FACTURA_CLIENT_PRODUCTE, true);
    });
  }

  await auxiliarSelect(data.factura_id ?? 0, 'facturesClients', 'factura_id', 'facConcepte');
  await auxiliarSelect(data.producte_id ?? 0, 'productes', 'producte_id', 'producte');
}
