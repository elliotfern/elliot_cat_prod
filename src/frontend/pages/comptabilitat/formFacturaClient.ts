import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

interface ProducteFactura {
  id: number;
  factura_id: number;
  producte_id: number | null;
  notes: string;
  preu: number;
}

interface FitxaFactura {
  [key: string]: unknown;
  id: number;
  numero_factura: number;
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
    const response = await fetchDataGet<ApiResponse<any>>(API_URLS.GET.FACTURA_CLIENT_ID(id), true);
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

  await auxiliarSelect(data.client_id ?? 0, 'clients', 'client_id', 'clientEmpresa');
  await auxiliarSelect(data.tipus_iva ?? 0, 'tipusIVA', 'tipus_iva', 'ivaPercen');
  await auxiliarSelect(data.estat ?? 0, 'estatFacturacio', 'estat', 'estat');
  await auxiliarSelect(data.metode_pagament ?? 0, 'tipusPagament', 'metode_pagament', 'tipus_notes');
  await auxiliarSelect(data.emissor_id ?? 0, 'emissors', 'emissor_id', 'nom');

  (window as any).facturaData = data; // si es edición
  await initProductesFactura();
}

/**
 * Preprocesa los datos del formulario de factura
 */
function preProcessFacturaFormData(rawData: Record<string, unknown>): Record<string, unknown> {
  const data: Record<string, unknown> = {};

  // Campos simples
  data.numero_factura = rawData.numero_factura ? Number(rawData.numero_factura) : null;
  data.emissor_id = rawData.emissor_id ? Number(rawData.emissor_id) : null;
  data.client_id = rawData.client_id ? Number(rawData.client_id) : null;
  data.concepte = rawData.concepte ?? null;
  data.data_factura = rawData.data_factura ?? null;
  data.data_venciment = rawData.data_venciment ?? null;
  data.base_imposable = rawData.base_imposable ? Number(rawData.base_imposable) : null;
  data.despeses_extra = rawData.despeses_extra ? Number(rawData.despeses_extra) : null;
  data.total_factura = rawData.total_factura ? Number(rawData.total_factura) : null;
  data.import_iva = rawData.import_iva ? Number(rawData.import_iva) : null;
  data.tipus_iva = rawData.tipus_iva ? Number(rawData.tipus_iva) : null;
  data.estat = rawData.estat ? Number(rawData.estat) : null;
  data.metode_pagament = rawData.metode_pagament ? Number(rawData.metode_pagament) : null;
  data.notes = rawData.notes ?? null;
  data.projecte_id = rawData.projecte_id ? Number(rawData.projecte_id) : null;
  data.arxiu_url = rawData.arxiu_url ?? null;
  data.recurrent = rawData.recurrent ? Boolean(Number(rawData.recurrent)) : false;
  data.frequencia = rawData.frequencia ?? null;

  // Productos: convierte arrays de inputs en objetos
  const producteIds = rawData.producte_id ?? [];
  const notesArr = rawData.producte_notes ?? [];
  const preusArr = rawData.producte_preu ?? [];

  if (Array.isArray(producteIds) && Array.isArray(notesArr) && Array.isArray(preusArr)) {
    data.productes = producteIds.map((id, idx) => ({
      producte_id: Number(id),
      notes: notesArr[idx] ?? '',
      preu: preusArr[idx] ? Number(preusArr[idx]) : 0,
    }));
  } else {
    data.productes = [];
  }

  return data;
}

/**
 * Añade funcionalidad de añadir productos a la factura
 */
export async function initProductesFactura() {
  const addBtn = document.getElementById('addProducte') as HTMLButtonElement;
  const tbody = document.querySelector('#tableProductesFactura tbody') as HTMLTableSectionElement;
  if (!addBtn || !tbody) return;

  // 1️⃣ Cargamos los productos desde la API
  const productesResponse = await fetchDataGet<ApiResponse<{ id: number; producte: string }[]>>(API_URLS.GET.PRODUCTES, true);
  const productes = productesResponse?.data ?? [];

  // Función para crear una fila de producto
  function crearFila(product?: { id: number; producte?: string; preu: number; notes: string }) {
    const row = document.createElement('tr');

    const optionsHTML = productes.map((p) => `<option value="${p.id}" ${product?.id === p.id ? 'selected' : ''}>${p.producte}</option>`).join('');

    row.innerHTML = `
      <td>
        <select name="producte_id[]" class="form-select">
          <option value="">Selecciona producte</option>
          ${optionsHTML}
        </select>
      </td>
      <td><input type="text" name="preu[]" class="form-control" value="${product?.preu ?? ''}" /></td>
      <td><input type="text" name="notes[]" class="form-control" value="${product?.notes ?? ''}" /></td>
      <td><button type="button" class="btn btn-danger btn-sm removeProducte">Eliminar</button></td>
    `;

    // Botón eliminar
    row.querySelector('.removeProducte')?.addEventListener('click', () => row.remove());

    tbody.appendChild(row);
  }

  // 2️⃣ Evento del botón "Afegir"
  addBtn.addEventListener('click', () => crearFila());

  // 3️⃣ Si ya tenemos productos en la factura (al editar), los renderizamos
  const existingProducts: ProducteFactura[] = (window as any).facturaData?.productes ?? [];
  existingProducts.forEach((p) => crearFila({ id: p.producte_id ?? 0, notes: p.notes, preu: p.preu }));
}
