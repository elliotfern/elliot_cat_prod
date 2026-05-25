import { api } from '../../core/api/client';
import { RenderTableOptions } from '../../types/TaulaDinamica';

function renderCellContent(content: string | HTMLElement | unknown): string {
  if (content == null) return '';

  if (typeof content === 'string') return content;

  if (content instanceof HTMLElement) return content.outerHTML;

  return String(content);
}

/**
 * Acceso seguro a propiedades anidadas: "data.factures"
 */
function getNestedValue(obj: any, path?: string): unknown {
  if (!path) return obj;

  return path.split('.').reduce((acc, key) => acc?.[key], obj);
}

/**
 * Normaliza cualquier respuesta API a array
 */
function extractArray(result: unknown, dataKey?: string): any[] {
  // 1. dataKey (nuevo sistema)
  const viaKey = getNestedValue(result, dataKey);

  if (Array.isArray(viaKey)) return viaKey;

  // 2. fallback clásico { data: [] }
  const legacyData = (result as any)?.data;

  if (Array.isArray(legacyData)) return legacyData;

  // 3. fallback { data: { x: [] } }
  if (legacyData && typeof legacyData === 'object') {
    const firstArray = Object.values(legacyData).find(Array.isArray);

    if (Array.isArray(firstArray)) return firstArray;
  }

  // 4. fallback directo
  if (Array.isArray(result)) return result;

  return [];
}

export async function renderDynamicTable<T extends Record<string, any>>(options: RenderTableOptions<T>): Promise<void> {
  const { url, columns, containerId, rowsPerPage = 15, filterKeys = [], filterByField, filterSplitBy, filterSplitTrim = true, dataKey } = options;

  const container = document.getElementById(containerId);

  if (!container) {
    console.error(`Contenedor #${containerId} no encontrado`);
    return;
  }

  let raw: any;

  try {
    raw = await api.get<any>(url);
  } catch (error) {
    console.error(error);

    container.innerHTML = `
      <div class="alert alert-danger">
        Error carregant dades
      </div>
    `;
    return;
  }

  // =========================
  // NORMALIZACIÓN SEGURA
  // =========================

  let data: T[] = [];

  if (Array.isArray(raw)) {
    // caso antiguo: API devuelve array directo
    data = raw;
  } else if (raw && typeof raw === 'object') {
    if (dataKey && Array.isArray(raw[dataKey])) {
      // caso nuevo: { factures: [] }
      data = raw[dataKey];
    } else if (Array.isArray(raw.data)) {
      // fallback antiguo wrapper
      data = raw.data;
    }
  }

  if (!Array.isArray(data) || data.length === 0) {
    container.innerHTML = `
      <div class="alert alert-info">
        No hi ha dades.
      </div>
    `;
    return;
  }

  // =========================
  // STATE
  // =========================

  let currentPage = 1;
  let filteredData = [...data];
  let activeButtonFilter: string | null = null;

  // =========================
  // ELEMENTS
  // =========================

  const searchInput = document.createElement('input');
  searchInput.className = 'form-control w-30 mb-3';
  searchInput.placeholder = 'Cercar...';

  const buttonContainer = document.createElement('div');
  buttonContainer.className = 'd-flex flex-wrap gap-2 mb-3';

  const table = document.createElement('table');
  table.classList.add('table', 'table-striped', 'table-hover', 'table-bordered', 'align-middle');

  const thead = document.createElement('thead');
  thead.classList.add('table-dark');

  const tbody = document.createElement('tbody');

  table.append(thead, tbody);

  const paginationNav = document.createElement('nav');
  paginationNav.setAttribute('aria-label', 'Paginació');

  const pagination = document.createElement('ul');
  pagination.className = 'pagination justify-content-center mt-3';

  paginationNav.appendChild(pagination);

  const totalRecords = document.createElement('div');
  totalRecords.className = 'text-muted small mt-2';

  // =========================
  // HELPERS
  // =========================

  const normalizeText = (text: string) =>
    text
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase();

  function getFilterParts(raw: unknown): string[] {
    const s = String(raw ?? '');
    if (!s) return [];

    if (!filterByField) return [s];

    const splitter = filterSplitBy?.[filterByField];

    if (!splitter) return [s];

    return s
      .split(splitter as any)
      .map((x) => (filterSplitTrim ? x.trim() : x))
      .filter(Boolean);
  }

  // =========================
  // FILTER LOGIC
  // =========================

  function applyFilters() {
    const search = normalizeText(searchInput.value);

    filteredData = data
      .filter((row) => {
        if (!activeButtonFilter) return true;
        if (!filterByField) return true;

        const fieldValue = getNestedValue(row, filterByField);

        if (Array.isArray(fieldValue)) {
          return fieldValue.map(String).includes(activeButtonFilter);
        }

        const parts = getFilterParts(fieldValue);

        if (parts.length > 1) {
          return parts.includes(activeButtonFilter);
        }

        return String(fieldValue ?? '') === activeButtonFilter;
      })
      .filter((row) => {
        if (!search) return true;

        return filterKeys.some((key) => normalizeText(String(getNestedValue(row, String(key)) ?? '')).includes(search));
      });

    currentPage = 1;
    renderTable();
  }

  // =========================
  // TABLE RENDER
  // =========================

  function renderTable() {
    thead.innerHTML = `
      <tr>
        ${columns.map((col) => `<th>${col.header}</th>`).join('')}
      </tr>
    `;

    const start = (currentPage - 1) * rowsPerPage;
    const rowsToShow = filteredData.slice(start, start + rowsPerPage);

    tbody.innerHTML = rowsToShow
      .map(
        (row) => `
          <tr>
            ${columns
              .map((col) => {
                const value = row[col.field];
                const rendered = col.render ? col.render(value, row) : String(value ?? '');

                return `<td>${renderCellContent(rendered)}</td>`;
              })
              .join('')}
          </tr>
        `
      )
      .join('');

    const totalPages = Math.ceil(filteredData.length / rowsPerPage);

    pagination.innerHTML = '';

    for (let i = 1; i <= totalPages; i++) {
      const li = document.createElement('li');
      li.className = 'page-item' + (i === currentPage ? ' active' : '');

      const a = document.createElement('a');
      a.className = 'page-link';
      a.href = '#';
      a.textContent = String(i);

      a.onclick = (e) => {
        e.preventDefault();
        currentPage = i;
        renderTable();
      };

      li.appendChild(a);
      pagination.appendChild(li);
    }

    totalRecords.textContent = `Número total de registres: ${filteredData.length}`;
  }

  // =========================
  // INIT
  // =========================

  searchInput.addEventListener('input', applyFilters);

  container.innerHTML = '';

  container.appendChild(searchInput);

  if (options.renderHeader) {
    const headerWrapper = document.createElement('div');
    headerWrapper.innerHTML = options.renderHeader({
      raw,
      data,
    });
    container.appendChild(headerWrapper);
  }

  const tableWrapper = document.createElement('div');
  tableWrapper.className = 'table-responsive';
  tableWrapper.appendChild(table);

  if (filterByField) {
    const values = data.map((row) => getNestedValue(row, filterByField)).filter(Boolean) as string[];

    // contar ocurrencias
    const counts = values.reduce((acc: Record<string, number>, value) => {
      const key = String(value);
      acc[key] = (acc[key] || 0) + 1;
      return acc;
    }, {});

    // únicos ordenados alfabéticamente
    const uniqueValues = Object.keys(counts).sort((a, b) => a.localeCompare(b, 'ca', { sensitivity: 'base' }));

    // limpiar contenedor
    buttonContainer.innerHTML = '';

    uniqueValues.forEach((value) => {
      const count = counts[value];

      const btn = document.createElement('button');

      const isActive = value === activeButtonFilter;

      btn.className = `
      btn btn-sm d-flex align-items-center gap-2
      ${isActive ? 'btn-primary' : 'btn-outline-primary'}
    `;

      btn.innerHTML = `
      <span>${value}</span>
      <span class="badge text-bg-secondary">${count}</span>
    `;

      btn.onclick = () => {
        activeButtonFilter = isActive ? null : value;
        applyFilters();
      };

      buttonContainer.appendChild(btn);
    });

    container.appendChild(buttonContainer);
  }

  container.appendChild(tableWrapper);
  container.appendChild(totalRecords);

  if (filteredData.length > rowsPerPage) {
    container.appendChild(paginationNav);
  }

  applyFilters();
}
