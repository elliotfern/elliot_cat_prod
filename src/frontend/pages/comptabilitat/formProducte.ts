import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { renderFormInputs } from '../../utils/renderInputsForm';

interface Producte {
  id?: number;
  producte: string;
  descripcio?: string;
  actiu: number;
  unitat?: string;
  preu_recomanat?: number;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export async function formProducte(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formProducte') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnProducte') as HTMLButtonElement | null;

  if (!form || !divTitol || !btnSubmit) return;

  let data: Partial<Producte> = {
    actiu: 1,
  };

  // Si es actualización, cargamos los datos
  if (isUpdate && id) {
    const response = await fetchDataGet<ApiResponse<Producte>>(API_URLS.GET.PRODUCTE_ID(id), true);
    if (!response || !response.data) return;

    data = response.data;

    divTitol.innerHTML = `<h2>Modificació Producte</h2>`;
    btnSubmit.textContent = 'Modificar Producte';

    renderFormInputs(data);

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'PUT', 'formProducte', API_URLS.PUT.PRODUCTE);
    });
  } else {
    // Creación
    divTitol.innerHTML = `<h2>Nou Producte</h2>`;
    btnSubmit.textContent = 'Crear Producte';

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'POST', 'formProducte', API_URLS.POST.PRODUCTE, true);
    });
  }
}