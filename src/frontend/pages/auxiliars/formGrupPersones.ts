import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { renderFormInputs } from '../../utils/renderInputsForm';

interface GrupPersones {
  [key: string]: unknown;
  status: string;
  message: string;

  // BINARY(16) (lo tratamos como string hex/uuid en frontend)
  id: string;

  grup_ca: string;
  grup_es: string;
  grup_en: string;
  grup_it: string;
  grup_fr: string;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

/**
 * Formulario Grups Persones
 * - isUpdate=true + id => carga datos y hace PUT
 * - isUpdate=false => create y hace POST
 */
export async function formGrupPersones(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formGrupPersones') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnGrupPersones') as HTMLButtonElement | null;

  // Estado inicial (para create)
  let data: Partial<GrupPersones> = {
    grup_ca: '',
    grup_es: '',
    grup_en: '',
    grup_it: '',
    grup_fr: '',
  };

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    // ⚠️ La API devuelve data: [ { ... } ]
    const response = await fetchDataGet<ApiResponse<GrupPersones[]>>(API_URLS.GET.PERSONES_GRUPS_ID(id), true);

    if (!response || !Array.isArray(response.data) || response.data.length === 0) return;

    const fitxa = response.data[0];

    // MUY IMPORTANTE: setea el hidden id (tu renderFormInputs puede no tocarlo)
    const inputId = document.getElementById('id') as HTMLInputElement | null;
    if (inputId) inputId.value = fitxa.id;

    renderFormInputs(fitxa);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formGrupPersones', API_URLS.PUT.PERSONES_GRUPS);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou Grup de persones</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formGrupPersones', API_URLS.POST.PERSONES_GRUPS, true);
    });
  }
}
