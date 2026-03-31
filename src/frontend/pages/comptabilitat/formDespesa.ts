import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

interface Despesa {
  id?: number;
  data: string; // 'YYYY-MM-DD'
  data_pagament?: string | null;
  concepte: string;
  proveidor_id: number;
  receptor_id: number;
  base_imposable: number;
  tipus_iva: number;
  import_iva: number;
  total: number;
  metode_pagament?: 'transferencia' | 'targeta' | 'efectiu' | 'domicili';
  pagat?: number; // 0/1
  categoria_id: number;
  subcategoria_id?: number | null;
  tipus_despesa?: 'professional' | 'personal';
  client_id?: number | null;
  projecte_id?: number | null;
  arxiu_url?: string | null;
  deduible?: number; // 0/1
  recurrent?: number; // 0/1
  frequencia?: 'mensual' | 'trimestral' | 'anual' | null;
  notes?: string | null;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

const first = <T>(d: T | T[] | null | undefined): T | null => (Array.isArray(d) ? (d[0] ?? null) : (d ?? null));

export async function formDespesa(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formDespesa') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnDespesa') as HTMLButtonElement | null;
  if (!form || !divTitol || !btnSubmit) return;

  let record: Partial<Despesa> = {};

  if (isUpdate && id) {
    const resp = await fetchDataGet<ApiResponse<Despesa | Despesa[]>>(API_URLS.GET.DESPESA_ID(id), true);
    const data = first(resp?.data);
    if (!data) return;

    record = data;

    divTitol.innerHTML = `<h2>Modificació Factura Despesa</h2>`;
    btnSubmit.textContent = 'Modificar Despesa';

    renderFormInputs(record);

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'PUT', 'formDespesa', API_URLS.PUT.DESPESA);
    });
  } else {
    divTitol.innerHTML = `<h2>Nova Factura de Despesa</h2>`;
    btnSubmit.textContent = 'Crear Despesa';

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'POST', 'formDespesa', API_URLS.POST.DESPESA, true);
    });
  }

  // --- Selects auxiliares ---
  await auxiliarSelect(record.proveidor_id ?? null, 'proveidors', 'proveidor_id', 'nom');
  await auxiliarSelect(record.categoria_id ?? null, 'categories_despeses', 'categoria_id', 'nom');
  await auxiliarSelect(record.subcategoria_id ?? null, 'sub_categories_despeses', 'subcategoria_id', 'nom');
  await auxiliarSelect(record.metode_pagament ?? null, 'metodes_pagament_despeses', 'metode_pagament', 'label');
  await auxiliarSelect(record.tipus_despesa ?? null, 'tipus_despeses', 'tipus_despesa', 'label');
  await auxiliarSelect(record.frequencia ?? null, 'frequencies', 'frequencia', 'label');
  await auxiliarSelect(record.projecte_id ?? null, 'projectes', 'projecte_id', 'name');
  await auxiliarSelect(record.client_id ?? null, 'clients', 'client_id', 'clientEmpresa');
}
