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
  espai_cat: string;
  municipi: number;
  comarca: number;
  provincia: number;
  comunitat: number;
  idUser: number;
  facIva: number;
  facEstat: number;
  facPaymentType: number;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export async function formFacturaClient(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formFacturaClient');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnFactura') as HTMLButtonElement;

  let data: Partial<Fitxa> = {
    comarca: 0,
    provincia: 0,
    comunitat: 0,
    estat: 0,
  };

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    const response = await fetchDataGet<ApiResponse<Fitxa>>(API_URLS.GET.FACTURA_CLIENT_ID(id), true);

    if (!response || !response.data) return;
    data = response.data;

    divTitol.innerHTML = `<h2>Modificació dades Factura client</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formFacturaClient', API_URLS.PUT.FACTURA_CLIENT);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nova factura</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formFacturaClient', API_URLS.POST.FACTURA_CLIENT, true);
    });
  }

  await auxiliarSelect(data.idUser ?? 0, 'clients', 'idUser', 'clientEmpresa');
  await auxiliarSelect(data.facIva ?? 0, 'tipusIVA', 'facIva', 'ivaPercen');
  await auxiliarSelect(data.facEstat ?? 0, 'estatFacturacio', 'facEstat', 'estat');
  await auxiliarSelect(data.facPaymentType ?? 0, 'tipusPagament', 'facPaymentType', 'tipusNom');
}
