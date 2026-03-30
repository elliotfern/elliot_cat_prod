import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

interface ProducteFactura {
  producte_id: number | null;
  descripcio: string;
  preu: number;
}

interface FitxaFactura {
  [key: string]: unknown;
  id: number;
  numero_factura: string;
  emissor_id: number | null;
  client_id: number;
  concepte: string;
  data_factura: string;
  data_venciment: string;
  base_imposable: number;
  despeses_extra: number | null;
  total_factura: number;
  import_iva: number;
  tipus_iva: number;
  estat: number;
  metode_pagament: number;
  notes: string | null;
  projecte_id: number | null;
  arxiu_url: string | null;
  recurrent: boolean;
  frequencia: 'mensual' | 'trimestral' | 'anual' | null;
  productes?: ProducteFactura[];
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export async function formFacturaClient(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formFacturaClient') as HTMLFormElement;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnFactura') as HTMLButtonElement;
  if (!divTitol || !btnSubmit || !form) return;

  let data: any = {};

  if (id && isUpdate) {
    const response = await fetchDataGet<ApiResponse<FitxaFactura>>(API_URLS.GET.FACTURA_CLIENT_ID(id), true);
    if (!response || !response.data) return;
    data = response.data;
    divTitol.innerHTML = `<h2>Modificació dades Factura client</h2>`;
    renderFormInputs(data);
    btnSubmit.textContent = 'Modificar dades';
    form.addEventListener('submit', (event) => transmissioDadesDB(event, 'PUT', 'formFacturaClient', API_URLS.PUT.FACTURA_CLIENT, true, 'none', preProcessFacturaFormData));
  } else {
    divTitol.innerHTML = `<h2>Creació de nova factura</h2>`;
    btnSubmit.textContent = 'Inserir dades';
    form.addEventListener('submit', (event) => transmissioDadesDB(event, 'POST', 'formFacturaClient', API_URLS.POST.FACTURA_CLIENT, true, 'none', preProcessFacturaFormData));
  }

  // Cargar selects
  await auxiliarSelect(data.client_id ?? 0, 'clients', 'client_id', 'clientEmpresa');
  await auxiliarSelect(data.tipus_iva ?? 0, 'tipusIVA', 'tipus_iva', 'ivaPercen');
  await auxiliarSelect(data.estat ?? 0, 'estatFacturacio', 'estat', 'estat');
  await auxiliarSelect(data.metode_pagament ?? 0, 'tipusPagament', 'metode_pagament', 'tipus_notes');
  await auxiliarSelect(data.emissor_id ?? 0, 'emissors', 'emissor_id', 'nom');
  await auxiliarSelect(data.projecte_id ?? 0, 'projectes', 'projecte_id', 'name');

  // Inicializar productos
  initProductesFactura(data.productes ?? []);
  initRecurrentFrecuencia(data);
}

/**
 * Preprocesa los datos del formulario de factura antes de enviar
 */
function preProcessFacturaFormData(rawData: Record<string, any>): Record<string, any> {
  return {
    numero_factura: rawData.numero_factura ?? null,
    emissor_id: rawData.emissor_id ? Number(rawData.emissor_id) : null,
    client_id: rawData.client_id ? Number(rawData.client_id) : null,
    concepte: rawData.concepte ?? null,
    data_factura: rawData.data_factura ?? null,
    data_venciment: rawData.data_venciment ?? null,
    base_imposable: rawData.base_imposable ? Number(rawData.base_imposable) : null,
    despeses_extra: rawData.despeses_extra ? Number(rawData.despeses_extra) : null,
    total_factura: rawData.total_factura ? Number(rawData.total_factura) : null,
    import_iva: rawData.import_iva ? Number(rawData.import_iva) : null,
    tipus_iva: rawData.tipus_iva ? Number(rawData.tipus_iva) : null,
    estat: rawData.estat ? Number(rawData.estat) : null,
    metode_pagament: rawData.metode_pagament ? Number(rawData.metode_pagament) : null,
    notes: rawData.notes ?? null,
    projecte_id: rawData.projecte_id ? Number(rawData.projecte_id) : null,
    arxiu_url: rawData.arxiu_url ?? null,
    recurrent: rawData.recurrent ? 1 : 0,
    frequencia: rawData.recurrent ? rawData.frequencia || null : null,
    productes: Array.isArray(rawData.producte_id)
      ? rawData.producte_id.map((id, idx) => ({
          producte_id: Number(id),
          descripcio: (rawData.descripcio ?? [])[idx] ?? '',
          preu: (rawData.preu ?? [])[idx] ? Number((rawData.preu ?? [])[idx]) : 0,
        }))
      : [],
  };
}

/**
 * Inicializa la tabla de productos y añade funcionalidad de añadir/eliminar
 */
export async function initProductesFactura(existingProducts: ProducteFactura[] = []) {
  const addBtn = document.getElementById('addProducte') as HTMLButtonElement;
  const tbody = document.querySelector('#tableProductesFactura tbody') as HTMLTableSectionElement;
  if (!addBtn || !tbody) return;

  // Cargar productos desde API
  const productesResponse = await fetchDataGet<ApiResponse<{ id: number; producte: string }[]>>(API_URLS.GET.PRODUCTES, true);
  const productes = productesResponse?.data ?? [];

  function crearFila(product?: ProducteFactura) {
    const row = document.createElement('tr');

    const optionsHTML = productes.map((p) => `<option value="${p.id}" ${product?.producte_id === p.id ? 'selected' : ''}>${p.producte}</option>`).join('');

    row.innerHTML = `
      <td>
        <select name="producte_id[]" class="form-select">
          <option value="">Selecciona producte</option>
          ${optionsHTML}
        </select>
      </td>
      <td><input type="text" name="preu[]" class="form-control" value="${product?.preu ?? ''}" /></td>
      <td><input type="text" name="descripcio[]" class="form-control" value="${product?.descripcio ?? ''}" /></td>
      <td><button type="button" class="btn btn-danger btn-sm removeProducte">Eliminar</button></td>
    `;

    row.querySelector('.removeProducte')?.addEventListener('click', () => row.remove());

    tbody.appendChild(row);
  }

  // Botón añadir
  addBtn.addEventListener('click', () => crearFila());

  // Renderizar productos existentes (al editar)
  existingProducts.forEach((p) => crearFila(p));
}

export function initRecurrentFrecuencia(data?: any) {
  const checkbox = document.getElementById('recurrent') as HTMLInputElement;
  const select = document.getElementById('frequencia') as HTMLSelectElement;

  if (!checkbox || !select) return;

  // Estado inicial (modo UPDATE)
  if (data) {
    checkbox.checked = Boolean(data.recurrent);
    select.disabled = !checkbox.checked;
    select.value = data.frequencia ?? '';
  }

  // Evento cambio checkbox
  checkbox.addEventListener('change', () => {
    if (checkbox.checked) {
      select.disabled = false;
    } else {
      select.disabled = true;
      select.value = ''; // limpiamos
    }
  });
}
