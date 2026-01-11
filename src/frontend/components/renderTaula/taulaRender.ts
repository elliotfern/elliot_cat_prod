import { RenderTableOptions } from '../../types/TaulaDinamica';

type ApiResult<T> = {
  status?: string;
  message?: string;
  data?: { items?: T[] } | T[];
  items?: T[];
};

function isObject(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null;
}

function pluckItems<T>(raw: unknown): T[] {
  // Si és array directe
  if (Array.isArray(raw)) return raw as T[];

  if (!isObject(raw)) return [];

  // raw.items
  if (Array.isArray(raw.items)) return raw.items as T[];

  // raw.data pot ser array o objecte amb items
  const data = raw.data;
  if (Array.isArray(data)) return data as T[];

  if (isObject(data) && Array.isArray(data.items)) return data.items as T[];

  return [];
}

export async function renderDynamicTable<T extends object>(options: RenderTableOptions<T>): Promise<void> {
  const { url, columns, containerId, rowsPerPage = 15, filterKeys = [], filterByField } = options;

  const container = document.getElementById(containerId);
  if (!container) {
    console.error(`Contenedor #${containerId} no encontrado`);
    return;
  }

  let result: ApiResult<T>;

  try {
    const response = await fetch(url);

    if (!response.ok) {
      container.innerHTML = `<div class="alert alert-info">Error HTTP ${response.status}</div>`;
      return;
    }

    result = (await response.json()) as ApiResult<T>;
  } catch (_e: unknown) {
    container.innerHTML = `<div class="alert alert-info">Error de xarxa o resposta invàlida</div>`;
    return;
  }

  if (result.status === 'error') {
    container.innerHTML = `<div class="alert alert-info">${result.message ?? 'No hi ha dades.'}</div>`;
    return;
  }

  const data: T[] = pluckItems<T>(result);

  if (!data.length) {
    container.innerHTML = `<div class="alert alert-info">${result.message ?? 'No hi ha dades.'}</div>`;
    return;
  }

  let currentPage = 1;
  let filteredData = [...data];
  let activeButtonFilter: string | null = null;

  // Crear input de búsqueda
  const searchInput = document.createElement('input');
  searchInput.style.marginBottom = '15px';
  searchInput.placeholder = 'Cercar...';

  // Crear contenedor de botones de filtro
  const buttonContainer = document.createElement('div');
  buttonContainer.className = 'filter-buttons';

  // Crear tabla y elementos relacionados
  const table = document.createElement('table');
  table.classList.add('table', 'table-striped');
  const thead = document.createElement('thead');
  thead.classList.add('table-primary');
  const tbody = document.createElement('tbody');
  const pagination = document.createElement('div');
  pagination.id = 'pagination';

  // Crear el numero total de registres
  const totalRecords = document.createElement('div');
  totalRecords.className = 'total-records';
  totalRecords.style.marginTop = '15px';
  totalRecords.style.fontSize = '12px';

  table.append(thead, tbody);

  // Normalizador para búsqueda
  const normalizeText = (text: string) =>
    text
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase();

  function applyFilters() {
    const search = normalizeText(searchInput.value);

    filteredData = data
      .filter((row) => {
        if (!activeButtonFilter) return true;
        if (!filterByField) return true;

        const fieldValue = row[filterByField];

        // Si el filtro es sobre un array (como grups), mira si incluye el filtro
        if (Array.isArray(fieldValue)) {
          return fieldValue.map(String).includes(activeButtonFilter);
        }

        return String(fieldValue) === activeButtonFilter;
      })
      .filter((row) => {
        if (search.length === 0) return true;
        return filterKeys.some((key) => normalizeText(String(row[key])).includes(search));
      });

    currentPage = 1;
    renderTable();
  }

  function renderFilterButtons() {
    if (!filterByField) return;

    let uniqueValues: string[] = [];

    const first = data[0]?.[filterByField];

    if (Array.isArray(first)) {
      // Si el campo es un array (como grups)
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
      uniqueValues = Array.from(new Set(data.map((row) => String(row[filterByField])))).filter(Boolean);
    }

    uniqueValues = uniqueValues.sort((a, b) => {
      return String(a).localeCompare(String(b), 'ca', { sensitivity: 'base' });
    });

    buttonContainer.innerHTML = '';

    const allButton = document.createElement('button');
    allButton.textContent = 'Tots';
    allButton.className = 'filter-btn';
    allButton.onclick = () => {
      activeButtonFilter = null;
      updateActiveButton(allButton);
      applyFilters();
    };
    buttonContainer.appendChild(allButton);

    uniqueValues.forEach((value) => {
      const button = document.createElement('button');
      button.textContent = String(value);
      button.className = 'filter-btn';
      button.onclick = () => {
        activeButtonFilter = value;
        updateActiveButton(button);
        applyFilters();
      };
      buttonContainer.appendChild(button);
    });

    updateActiveButton(allButton);
  }

  function updateActiveButton(activeButton: HTMLButtonElement) {
    const buttons = buttonContainer.querySelectorAll('.filter-btn');
    buttons.forEach((btn) => btn.classList.remove('active'));
    activeButton.classList.add('active');
  }

  function renderTable() {
    // Cabecera
    thead.innerHTML = `<tr>${columns.map((col) => `<th>${col.header}</th>`).join('')}</tr>`;

    // Paginación
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const rowsToShow = filteredData.slice(start, end);

    tbody.innerHTML = rowsToShow
      .map(
        (row) =>
          `<tr>${columns
            .map((col) => {
              const value = row[col.field];
              return `<td>${col.render ? col.render(value, row) : String(value ?? '')}</td>`;
            })
            .join('')}</tr>`
      )
      .join('');

    const totalPages = Math.ceil(filteredData.length / rowsPerPage);
    pagination.innerHTML = '';
    for (let i = 1; i <= totalPages; i++) {
      const link = document.createElement('a');
      link.textContent = i.toString();
      link.href = '#';
      link.className = 'pagination-link' + (i === currentPage ? ' current-page' : '');
      link.onclick = (e) => {
        e.preventDefault();
        currentPage = i;
        renderTable();
      };
      pagination.appendChild(link);
    }

    totalRecords.textContent = `Número total de registres: ${filteredData.length}`;
  }

  // Eventos
  searchInput.addEventListener('input', applyFilters);

  // Render inicial
  container.innerHTML = '';
  container.appendChild(searchInput);
  if (filterByField) {
    container.appendChild(buttonContainer);
    renderFilterButtons();
  }
  container.appendChild(table);
  container.appendChild(totalRecords);
  container.appendChild(pagination);

  applyFilters(); // inicia renderizado con filtros aplicados
}
