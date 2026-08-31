import { TaulaDinamica } from '../../../types/TaulaDinamica';

export type TaulaRenderOptions<T extends object> = {
  data: T[];
  columns: Array<TaulaDinamica<T>>;
  containerId: string;
  rowsPerPage?: number;
  filterKeys?: Array<keyof T>;

  // Filtro simple
  filterByField?: string;

  // Permite varios niveles de filtrado
  filterByFields?: string[];

  // Split de valores para filtros
  filterSplitBy?: Partial<Record<keyof T, string | RegExp>>;
  filterSplitTrim?: boolean;

  renderHeader?: (data: T[]) => string;
  filterLabels?: Record<string, string>;
};

function renderCellContent(content: string | HTMLElement | unknown): string {
  if (content == null) return '';

  if (typeof content === 'string') return content;

  if (content instanceof HTMLElement) return content.outerHTML;

  return String(content);
}

/**
 * Acceso seguro a propiedades anidadas:
 *
 * "data.factures"
 * "projecte.nom"
 */
function getNestedValue(obj: unknown, path?: string): unknown {
  if (!path) return obj;

  return path.split('.').reduce((acc: any, key) => acc?.[key], obj);
}

export function taulaDinamica<T extends object>(options: TaulaRenderOptions<T>): void {
  const { data, columns, containerId, rowsPerPage = 15, filterKeys = [], filterByField, filterByFields = [], filterLabels, filterSplitBy, filterSplitTrim = true } = options;

  const filterFields = filterByFields.length > 0 ? filterByFields : filterByField ? [filterByField] : [];

  const container = document.getElementById(containerId);

  if (!container) {
    console.error(`Contenidor #${containerId} no trobat`);
    return;
  }

  if (!Array.isArray(data) || data.length === 0) {
    container.innerHTML = `
      <div class="alert alert-info">
        No hi ha dades disponibles.
      </div>
    `;
    return;
  }

  // =========================
  // STATE
  // =========================

  let currentPage = 1;
  let filteredData = [...data];

  let activeButtonFilters: Record<string, string> = {};

  let sortField: string | null = null;
  let sortDirection: 'asc' | 'desc' | null = null;

  // =========================
  // ELEMENTS
  // =========================

  const searchInput = document.createElement('input');
  searchInput.className = 'form-control w-30 mb-3';
  searchInput.placeholder = 'Cercar...';

  const buttonContainer = document.createElement('div');
  buttonContainer.className = 'mb-3';

  const table = document.createElement('table');

  table.classList.add('table', 'table-striped', 'table-hover', 'table-bordered', 'align-middle');

  const thead = document.createElement('thead');
  thead.classList.add('table-dark');

  const tbody = document.createElement('tbody');

  table.append(thead, tbody);

  const paginationNav = document.createElement('nav');
  paginationNav.setAttribute('aria-label', 'Paginació');

  const pagination = document.createElement('ul');
  pagination.className = 'pagination justify-content-center flex-wrap mt-3';

  paginationNav.appendChild(pagination);

  const totalRecords = document.createElement('div');
  totalRecords.className = 'text-muted small mt-2';

  // =========================
  // HELPERS
  // =========================

  const normalizeText = (text: string): string =>
    text
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase();

  function getFilterParts(raw: unknown, field?: string): string[] {
    const s = String(raw ?? '');

    if (!s) {
      return [];
    }

    const filterField = field ?? filterByField;

    if (!filterField) {
      return [s];
    }

    const splitter = filterSplitBy?.[filterField as keyof T];

    if (!splitter) {
      return [s];
    }

    return s
      .split(splitter as any)
      .map((x) => (filterSplitTrim ? x.trim() : x))
      .filter(Boolean);
  }

  // =========================
  // FILTERS
  // =========================

  function renderFilters(): void {
    if (filterFields.length === 0) {
      return;
    }

    buttonContainer.innerHTML = '';

    filterFields.forEach((field) => {
      // ============================================================
      // CONTADORES
      // Calculamos los registros disponibles teniendo en cuenta
      // los otros filtros activos, pero NO el filtro actual.
      // ============================================================

      const rowsForCounts = data.filter((row) => {
        return Object.entries(activeButtonFilters).every(([activeField, activeValue]) => {
          if (activeField === field) {
            return true;
          }

          if (!activeValue) {
            return true;
          }

          const fieldValue = getNestedValue(row, activeField);

          if (Array.isArray(fieldValue)) {
            return fieldValue.map(String).includes(activeValue);
          }

          const parts = getFilterParts(fieldValue, activeField);

          if (parts.length > 1) {
            return parts.includes(activeValue);
          }

          return String(fieldValue ?? '') === activeValue;
        });
      });

      // ============================================================
      // VALORES
      // Siempre obtenemos los valores del
      // dataset original.
      // ============================================================

      const allValues = data.flatMap((row) => getFilterParts(getNestedValue(row, field), field));

      const uniqueValues = [...new Set(allValues)].sort((a, b) =>
        a.localeCompare(b, 'ca', {
          sensitivity: 'base',
        })
      );

      // ============================================================
      // CONTADORES
      // ============================================================

      const valuesForCounts = rowsForCounts.flatMap((row) => getFilterParts(getNestedValue(row, field), field));

      const counts = valuesForCounts.reduce((acc: Record<string, number>, value) => {
        const key = String(value);

        acc[key] = (acc[key] || 0) + 1;

        return acc;
      }, {});

      // ============================================================
      // GRUPO DEL FILTRO
      // ============================================================

      const filterGroup = document.createElement('div');

      filterGroup.className = 'mb-3';

      const title = document.createElement('div');

      title.className = 'fw-semibold mb-2';

      title.textContent = filterLabels?.[field] ?? field;

      filterGroup.appendChild(title);

      const buttons = document.createElement('div');

      buttons.className = 'd-flex flex-wrap gap-2';

      // ============================================================
      // BOTONES
      // ============================================================

      uniqueValues.forEach((value) => {
        const count = counts[value] ?? 0;

        let labelValue = value;

        if (field === 'actiu') {
          labelValue = value === '1' ? 'Actius' : 'Arxivats';
        }

        const btn = document.createElement('button');

        btn.type = 'button';

        btn.className = `
            btn btn-sm d-flex align-items-center gap-2
            ${activeButtonFilters[field] === value ? 'btn-primary' : 'btn-outline-primary'}
          `;

        btn.innerHTML = `
            <span>${labelValue}</span>
            <span class="badge text-bg-secondary">
              ${count}
            </span>
          `;

        btn.onclick = () => {
          if (activeButtonFilters[field] === value) {
            delete activeButtonFilters[field];
          } else {
            activeButtonFilters[field] = value;
          }

          renderFilters();
          applyFilters();
        };

        buttons.appendChild(btn);
      });

      filterGroup.appendChild(buttons);

      buttonContainer.appendChild(filterGroup);
    });
  }

  // =========================
  // SORT
  // =========================

  function applySort(): void {
    if (!sortField || !sortDirection) {
      return;
    }

    const field = sortField;
    const direction = sortDirection;

    const column = columns.find((col) => String(col.field) === field);

    if (!column) {
      return;
    }

    filteredData.sort((a, b) => {
      const valueA = column.sortValue ? column.sortValue(a) : getNestedValue(a, field);

      const valueB = column.sortValue ? column.sortValue(b) : getNestedValue(b, field);

      const aString = String(valueA ?? '');

      const bString = String(valueB ?? '');

      const comparison = aString.localeCompare(bString, 'ca', {
        numeric: true,
        sensitivity: 'base',
      });

      return direction === 'asc' ? comparison : -comparison;
    });
  }

  // =========================
  // FILTER LOGIC
  // =========================

  function applyFilters(): void {
    const search = normalizeText(searchInput.value);

    filteredData = data
      .filter((row) => {
        return Object.entries(activeButtonFilters).every(([field, value]) => {
          const fieldValue = getNestedValue(row, field);

          if (Array.isArray(fieldValue)) {
            return fieldValue.map(String).includes(value);
          }

          const parts = getFilterParts(fieldValue, field);

          if (parts.length > 1) {
            return parts.includes(value);
          }

          return String(fieldValue ?? '') === value;
        });
      })
      .filter((row) => {
        if (!search) {
          return true;
        }

        return filterKeys.some((key) => normalizeText(String(getNestedValue(row, String(key)) ?? '')).includes(search));
      });

    applySort();

    currentPage = 1;

    renderTable();
  }

  // =========================
  // TABLE RENDER
  // =========================

  function renderTable(): void {
    thead.innerHTML = `
      <tr>
        ${columns
          .map((col) => {
            let indicator = '';

            if (sortField === String(col.field)) {
              if (sortDirection === 'asc') {
                indicator = ' ↑';
              } else if (sortDirection === 'desc') {
                indicator = ' ↓';
              }
            }

            return `
              <th
                data-sort-field="${String(col.field)}"
                style="cursor: pointer;"
              >
                ${col.header}${indicator}
              </th>
            `;
          })
          .join('')}
      </tr>
    `;

    thead.querySelectorAll('th').forEach((th) => {
      th.addEventListener('click', () => {
        const field = th.dataset.sortField;

        if (!field) {
          return;
        }

        if (sortField !== field) {
          sortField = field;
          sortDirection = 'asc';
        } else if (sortDirection === 'asc') {
          sortDirection = 'desc';
        } else {
          sortField = null;
          sortDirection = null;
        }

        applySort();
        renderTable();
      });
    });

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

                  return `
                    <td>
                      ${renderCellContent(rendered)}
                    </td>
                  `;
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

    headerWrapper.innerHTML = options.renderHeader(data);

    container.appendChild(headerWrapper);
  }

  const tableWrapper = document.createElement('div');

  tableWrapper.className = 'table-responsive';

  tableWrapper.appendChild(table);

  if (filterFields.length > 0) {
    container.appendChild(buttonContainer);

    renderFilters();
  }

  container.appendChild(tableWrapper);

  container.appendChild(totalRecords);

  if (filteredData.length > rowsPerPage) {
    container.appendChild(paginationNav);
  }

  applyFilters();
}
