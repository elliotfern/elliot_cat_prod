import { api } from '../../core/api/client';
import { RenderTableOptions } from '../../types/TaulaDinamica';

function renderCellContent(content: string | HTMLElement | unknown): string {
  if (content == null) return '';

  if (typeof content === 'string') {
    return content;
  }

  if (content instanceof HTMLElement) {
    return content.outerHTML;
  }

  return String(content);
}

export async function renderDynamicTable<T extends Record<string, any>>(options: RenderTableOptions<T>): Promise<void> {
  const { url, columns, containerId, rowsPerPage = 15, filterKeys = [], filterByField, filterSplitBy, filterSplitTrim = true } = options;

  const container = document.getElementById(containerId);

  if (!container) {
    console.error(`Contenedor #${containerId} no encontrado`);
    return;
  }

  // =========================
  // API
  // =========================

  let data: T[] = [];

  try {
    data = await api.get<T[]>(url);
  } catch (error) {
    console.error(error);

    container.innerHTML = `
      <div class="alert alert-danger">
        Error carregant dades
      </div>
    `;

    return;
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

  function applyFilters() {
    const search = normalizeText(searchInput.value);

    filteredData = data
      .filter((row) => {
        if (!activeButtonFilter) return true;

        if (!filterByField) return true;

        const fieldValue = row[filterByField];

        // ARRAY
        if (Array.isArray(fieldValue)) {
          return fieldValue.map(String).includes(activeButtonFilter);
        }

        // SPLIT
        const parts = getFilterParts(fieldValue);

        if (parts.length > 1) {
          return parts.includes(activeButtonFilter);
        }

        // SIMPLE
        return String(fieldValue) === activeButtonFilter;
      })
      .filter((row) => {
        if (search.length === 0) {
          return true;
        }

        return filterKeys.some((key) => normalizeText(String(row[key] ?? '')).includes(search));
      });

    currentPage = 1;

    renderTable();
  }

  // =========================
  // FILTER BUTTONS
  // =========================

  function updateActiveButton(activeButton: HTMLButtonElement) {
    const buttons = buttonContainer.querySelectorAll('.filter-btn');

    buttons.forEach((btn) => {
      btn.classList.remove('active');
    });

    activeButton.classList.add('active');
  }

  function renderFilterButtons() {
    if (!filterByField) return;

    let uniqueValues: string[] = [];

    const first = data[0]?.[filterByField];

    // ARRAY FIELD
    if (Array.isArray(first)) {
      uniqueValues = Array.from(
        new Set(
          data
            .flatMap((row) => {
              const v = row[filterByField];

              return Array.isArray(v) ? v : [];
            })
            .map(String)
        )
      ).filter(Boolean);
    } else {
      // SPLIT FIELD
      const splitter = filterSplitBy?.[filterByField];

      if (splitter) {
        uniqueValues = Array.from(new Set(data.flatMap((row) => getFilterParts(row[filterByField])).map(String))).filter(Boolean);
      } else {
        // SIMPLE FIELD
        uniqueValues = Array.from(new Set(data.map((row) => String(row[filterByField] ?? '')))).filter(Boolean);
      }
    }

    uniqueValues = uniqueValues.sort((a, b) =>
      a.localeCompare(b, 'ca', {
        sensitivity: 'base',
      })
    );

    buttonContainer.innerHTML = '';

    // TOTS
    const allButton = document.createElement('button');

    allButton.textContent = 'Tots';
    allButton.className = 'btn btn-outline-primary btn-sm filter-btn';

    allButton.onclick = () => {
      activeButtonFilter = null;

      updateActiveButton(allButton);

      applyFilters();
    };

    buttonContainer.appendChild(allButton);

    // VALUES
    uniqueValues.forEach((value) => {
      const button = document.createElement('button');

      button.textContent = value;

      button.className = 'btn btn-primary btn-sm filter-btn';

      button.onclick = () => {
        activeButtonFilter = value;

        updateActiveButton(button);

        applyFilters();
      };

      buttonContainer.appendChild(button);
    });

    updateActiveButton(allButton);
  }

  // =========================
  // TABLE
  // =========================

  function renderTable() {
    // HEADER

    thead.innerHTML = `
      <tr>
        ${columns.map((col) => `<th>${col.header}</th>`).join('')}
      </tr>
    `;

    // PAGINATION

    const start = (currentPage - 1) * rowsPerPage;

    const end = start + rowsPerPage;

    const rowsToShow = filteredData.slice(start, end);

    // ROWS

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

    // PAGINATION BUTTONS

    const totalPages = Math.ceil(filteredData.length / rowsPerPage);

    pagination.innerHTML = '';

    for (let i = 1; i <= totalPages; i++) {
      const li = document.createElement('li');

      li.className = 'page-item' + (i === currentPage ? ' active' : '');

      const a = document.createElement('a');

      a.className = 'page-link';
      a.href = '#';
      a.textContent = String(i);

      a.addEventListener('click', (e) => {
        e.preventDefault();

        if (i === currentPage) return;

        currentPage = i;

        renderTable();
      });

      li.appendChild(a);

      pagination.appendChild(li);
    }

    totalRecords.textContent = `
      Número total de registres: ${filteredData.length}
    `;
  }

  // =========================
  // EVENTS
  // =========================

  searchInput.addEventListener('input', applyFilters);

  // =========================
  // INITIAL RENDER
  // =========================

  const tableWrapper = document.createElement('div');

  tableWrapper.className = 'table-responsive';

  tableWrapper.appendChild(table);

  container.innerHTML = '';

  if (options.renderHeader) {
    const headerWrapper = document.createElement('div');

    headerWrapper.innerHTML = options.renderHeader(data);

    container.appendChild(headerWrapper);
  }

  container.appendChild(searchInput);

  if (filterByField) {
    container.appendChild(buttonContainer);

    renderFilterButtons();
  }

  container.appendChild(tableWrapper);

  container.appendChild(totalRecords);

  if (filteredData.length > rowsPerPage) {
    container.appendChild(paginationNav);
  }

  applyFilters();
}
