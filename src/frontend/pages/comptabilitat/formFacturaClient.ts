import { api } from '../../core/api/client';
import { Factura, ProducteFactura } from '../../types/Factura';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formFacturaClient(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formFacturaClient') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnFactura') as HTMLButtonElement | null;

  if (!divTitol || !btnSubmit || !form) return;

  let data: Partial<Factura> = {};

  /*
   * ============================================================
   * UPDATE
   * ============================================================
   */

  if (id && isUpdate) {
    try {
      /*
       * La API devuelve:
       *
       * {
       *   factura: {...},
       *   productes: [...]
       * }
       *
       * Por tanto NO podemos tratar directamente la respuesta
       * como una Factura.
       */

      const response = await api.get<{
        factura: Factura;
        productes: ProducteFactura[];
      }>(API_URLS.GET.FACTURA_CLIENT_ID, {
        id,
      });

      data = response.factura ?? {};

      /*
       * Inicializar productos existentes
       */
      await initProductesFactura(response.productes ?? []);
    } catch (error) {
      console.error('Error obtenint la factura:', error);
      return;
    }

    /*
     * Título
     */
    divTitol.innerHTML = `
      <h2>Modificació dades Factura client</h2>
    `;

    /*
     * Pintar inputs del formulario
     */
    renderFormInputs(data);

    /*
     * Hidden fields
     */
    const idValue = document.querySelector<HTMLInputElement>('#id');
    const idFactura = document.querySelector<HTMLInputElement>('#numero_factura');

    if (idValue) {
      idValue.value = data.id != null ? String(data.id) : '';
    }

    if (idFactura) {
      idFactura.value = data.numero_factura != null ? String(data.numero_factura) : '';
    }

    /*
     * Botón
     */
    btnSubmit.textContent = 'Modificar dades';

    /*
     * Submit UPDATE
     */
    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'PUT', 'formFacturaClient', API_URLS.PUT.FACTURA_CLIENT, true, 'none', preProcessFacturaFormData);
    });

    /*
     * ============================================================
     * CREATE
     * ============================================================
     */
  } else {
    divTitol.innerHTML = `
      <h2>Creació de nova factura</h2>
    `;

    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'POST', 'formFacturaClient', API_URLS.POST.FACTURA_CLIENT, true, 'none', preProcessFacturaFormData);
    });

    await initProductesFactura([]);
  }

  /*
   * ============================================================
   * SELECTS AUXILIARS
   * ============================================================
   */

  await auxiliarSelect(data.client_id ?? '', 'clients', 'client_id', 'empresa');

  await auxiliarSelect(data.tipus_iva ?? 0, 'tipusIVA', 'tipus_iva', 'ivaPercen');

  await auxiliarSelect(data.estat_id ?? 0, 'estatFacturacio', 'estat', 'estat');

  await auxiliarSelect(data.metode_pagament ?? 0, 'tipusPagament', 'metode_pagament', 'tipus_notes');

  await auxiliarSelect(data.emissor_id ?? '', 'emissors', 'emissor_id', 'nom');

  await auxiliarSelect(data.projecte_id ?? 0, 'projectes', 'projecte_id', 'name');

  /*
   * Factura recurrent
   */
  initRecurrentFrecuencia(data);
}

/**
 * ============================================================
 * PREPROCESAR FORMULARIO
 * ============================================================
 */
function preProcessFacturaFormData(rawData: Record<string, any>): Record<string, any> {
  /*
   * ID factura
   */
  const idInput = document.querySelector<HTMLInputElement>('#id');

  const idValue = idInput?.value ? String(idInput.value) : null;

  /*
   * Número factura
   */
  const numeroFacturaInput = document.querySelector<HTMLInputElement>('#numero_factura');

  const numeroFactura = numeroFacturaInput?.value || null;

  /*
   * ============================================================
   * PRODUCTES
   * ============================================================
   */

  const producteIds = Array.from(document.querySelectorAll<HTMLSelectElement>('select[name="producte_id[]"]'))
    .map((el) => el.value)
    .filter((value) => value !== '');

  const descripcions = Array.from(document.querySelectorAll<HTMLInputElement>('input[name="descripcio[]"]')).map((el) => el.value);

  const preus = Array.from(document.querySelectorAll<HTMLInputElement>('input[name="preu[]"]')).map((el) => {
    const value = el.value.replace(',', '.');

    return value !== '' ? Number(value) : 0;
  });

  const productes = producteIds.map((producteId, index) => ({
    producte_id: producteId,
    descripcio: descripcions[index] ?? '',
    preu: preus[index] ?? 0,
  }));

  /*
   * ============================================================
   * RESULTADO
   * ============================================================
   */

  return {
    /*
     * Factura
     */
    id: idValue,

    numero_factura: numeroFactura,

    /*
     * IMPORTANTE:
     * client_id y emissor_id son UUID.
     * NO utilizar Number().
     */
    client_id: rawData.client_id != null && rawData.client_id !== '' ? String(rawData.client_id) : null,

    emissor_id: rawData.emissor_id != null && rawData.emissor_id !== '' ? String(rawData.emissor_id) : null,

    concepte: rawData.concepte ?? null,

    data_factura: rawData.data_factura ?? null,

    data_venciment: rawData.data_venciment ?? null,

    /*
     * Importes
     */
    base_imposable: rawData.base_imposable != null && rawData.base_imposable !== '' ? Number(String(rawData.base_imposable).replace(',', '.')) : 0,

    despeses_extra: rawData.despeses_extra != null && rawData.despeses_extra !== '' ? Number(String(rawData.despeses_extra).replace(',', '.')) : 0,

    total_factura: rawData.total_factura != null && rawData.total_factura !== '' ? Number(String(rawData.total_factura).replace(',', '.')) : 0,

    import_iva: rawData.import_iva != null && rawData.import_iva !== '' ? Number(String(rawData.import_iva).replace(',', '.')) : 0,

    /*
     * Selects numéricos
     */
    tipus_iva: rawData.tipus_iva != null && rawData.tipus_iva !== '' ? Number(rawData.tipus_iva) : 0,

    estat: rawData.estat != null && rawData.estat !== '' ? Number(rawData.estat) : 0,

    metode_pagament: rawData.metode_pagament != null && rawData.metode_pagament !== '' ? Number(rawData.metode_pagament) : 0,

    projecte_id: rawData.projecte_id != null && rawData.projecte_id !== '' ? Number(rawData.projecte_id) : 0,

    /*
     * Otros campos
     */
    notes: rawData.notes ?? null,

    arxiu_url: rawData.arxiu_url ?? null,

    recurrent: rawData.recurrent ? 1 : 0,

    frequencia: rawData.recurrent ? rawData.frequencia || null : null,

    /*
     * Productos
     */
    productes,
  };
}

/**
 * ============================================================
 * PRODUCTES DE LA FACTURA
 * ============================================================
 */
export async function initProductesFactura(existingProducts: ProducteFactura[] = []) {
  const addBtn = document.getElementById('addProducte') as HTMLButtonElement | null;

  const tbody = document.querySelector('#tableProductesFactura tbody') as HTMLTableSectionElement | null;

  if (!addBtn || !tbody) return;

  // Referencia segura para TypeScript dentro de las funciones internas
  const tableBody = tbody;

  /*
   * Cargar catálogo de productos
   */
  let productes: {
    id: string;
    producte: string;
  }[] = [];

  try {
    productes = await api.get<
      {
        id: string;
        producte: string;
      }[]
    >(API_URLS.GET.PRODUCTES);
  } catch (error) {
    console.error('Error carregant productes:', error);
    return;
  }

  /*
   * Crear fila
   */
  function crearFila(product?: ProducteFactura) {
    const row = document.createElement('tr');

    const optionsHTML = productes
      .map(
        (p) => `
          <option
            value="${p.id}"
            ${product?.producte_id === p.id ? 'selected' : ''}
          >
            ${p.producte}
          </option>
        `
      )
      .join('');

    row.innerHTML = `
      <td>
        <select
          name="producte_id[]"
          class="form-select"
        >
          <option value="">
            Selecciona producte
          </option>

          ${optionsHTML}
        </select>
      </td>

      <td>
        <input
          type="text"
          name="preu[]"
          class="form-control"
          value="${product?.preu ?? ''}"
        />
      </td>

      <td>
        <input
          type="text"
          name="descripcio[]"
          class="form-control"
          value="${product?.descripcio ?? ''}"
        />
      </td>

      <td>
        <button
          type="button"
          class="btn btn-danger btn-sm removeProducte"
        >
          Eliminar
        </button>
      </td>
    `;

    row.querySelector('.removeProducte')?.addEventListener('click', () => {
      row.remove();
    });

    tableBody.appendChild(row);
  }

  /*
   * Añadir producto
   */
  addBtn.addEventListener('click', () => {
    crearFila();
  });

  /*
   * Productos existentes
   */
  existingProducts.forEach((product) => {
    crearFila(product);
  });
}

/**
 * ============================================================
 * FACTURA RECURRENT
 * ============================================================
 */
export function initRecurrentFrecuencia(data?: Partial<Factura>) {
  const checkbox = document.getElementById('recurrent') as HTMLInputElement | null;

  const select = document.getElementById('frequencia') as HTMLSelectElement | null;

  if (!checkbox || !select) return;

  /*
   * Estado inicial
   */
  if (data) {
    checkbox.checked = Boolean(data.recurrent);

    select.disabled = !checkbox.checked;

    select.value = data.frequencia ?? '';
  }

  /*
   * Cambio checkbox
   */
  checkbox.addEventListener('change', () => {
    if (checkbox.checked) {
      select.disabled = false;
    } else {
      select.disabled = true;
      select.value = '';
    }
  });
}
