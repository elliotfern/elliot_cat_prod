import { Esdeveniment } from '../../types/Esdeveniment';
import { getIsAdmin } from '../../services/auth/isAdmin';

// -------------------------
// RENDER PERSONES
// -------------------------

async function renderPersones(esdeveniment: Esdeveniment, isAdmin: boolean): Promise<HTMLElement | null> {
  if (esdeveniment.persones.length === 0) return null;

  const section = document.createElement('section');
  section.className = 'mb-4';

  const title = document.createElement('h2');
  title.className = 'h4 mb-3';
  title.textContent = 'Persones relacionades:';

  const table = document.createElement('table');
  table.className = 'table table-striped table-hover table-bordered align-middle';

  const thead = document.createElement('thead');
  thead.classList.add('table-dark');
  const headerRow = document.createElement('tr');

  const nameHeader = document.createElement('th');
  nameHeader.scope = 'col';
  nameHeader.textContent = 'Nom';

  headerRow.appendChild(nameHeader);

  if (isAdmin) {
    const actionsHeader = document.createElement('th');
    actionsHeader.scope = 'col';
    actionsHeader.className = 'text-end';
    actionsHeader.textContent = 'Accions';
    headerRow.appendChild(actionsHeader);
  }

  thead.appendChild(headerRow);

  const tbody = document.createElement('tbody');

  for (const persona of esdeveniment.persones) {
    const row = document.createElement('tr');

    const nameCell = document.createElement('td');
    const link = document.createElement('a');

    link.href = `/gestio/base-dades-persones/fitxa-persona/${encodeURIComponent(persona.slug)}`;
    link.textContent = `${persona.nom} ${persona.cognoms}`.trim();

    nameCell.appendChild(link);
    row.appendChild(nameCell);

    if (isAdmin) {
      const actionsCell = document.createElement('td');
      actionsCell.className = 'text-end';

      const editButton = document.createElement('a');
      editButton.href = `/gestio/base-dades-persones/modifica-persona/${encodeURIComponent(persona.slug)}/`;
      editButton.className = 'btn btn-secondary btn-sm';
      editButton.textContent = 'Modifica';

      actionsCell.appendChild(editButton);
      row.appendChild(actionsCell);
    }

    tbody.appendChild(row);
  }

  table.append(thead, tbody);

  const responsive = document.createElement('div');
  responsive.className = 'table-responsive';
  responsive.appendChild(table);

  section.append(title, responsive);

  return section;
}

// -------------------------
// RENDER ORGANITZACIONS
// -------------------------

async function renderOrganitzacions(esdeveniment: Esdeveniment, isAdmin: boolean): Promise<HTMLElement | null> {
  if (esdeveniment.organitzacions.length === 0) return null;

  const section = document.createElement('section');
  section.className = 'mb-4';

  const title = document.createElement('h2');
  title.className = 'h4 mb-3';
  title.textContent = 'Organitzacions relacionades';

  const table = document.createElement('table');
  table.className = 'table table-striped table-hover table-bordered align-middle';

  const thead = document.createElement('thead');
  thead.classList.add('table-dark');
  const headerRow = document.createElement('tr');

  const nameHeader = document.createElement('th');
  nameHeader.scope = 'col';
  nameHeader.textContent = 'Organització';

  headerRow.appendChild(nameHeader);

  const acronymHeader = document.createElement('th');
  acronymHeader.scope = 'col';
  acronymHeader.textContent = 'Sigla';

  headerRow.appendChild(acronymHeader);

  if (isAdmin) {
    const actionsHeader = document.createElement('th');
    actionsHeader.scope = 'col';
    actionsHeader.className = 'text-end';
    actionsHeader.textContent = 'Accions';
    headerRow.appendChild(actionsHeader);
  }

  thead.appendChild(headerRow);

  const tbody = document.createElement('tbody');

  for (const organitzacio of esdeveniment.organitzacions) {
    const row = document.createElement('tr');

    const nameCell = document.createElement('td');
    const link = document.createElement('a');

    link.href = `/gestio/historia/fitxa-organitzacio/${encodeURIComponent(organitzacio.slug)}`;
    link.textContent = organitzacio.organitzacio;

    nameCell.appendChild(link);
    row.appendChild(nameCell);

    const acronymCell = document.createElement('td');
    acronymCell.textContent = organitzacio.sigla || '';
    row.appendChild(acronymCell);

    if (isAdmin) {
      const actionsCell = document.createElement('td');
      actionsCell.className = 'text-end';

      const editButton = document.createElement('a');
      editButton.href = `/gestio/historia/modifica-organitzacio/${encodeURIComponent(organitzacio.id)}/`;
      editButton.className = 'btn btn-secondary btn-sm';
      editButton.textContent = 'Modifica';

      actionsCell.appendChild(editButton);
      row.appendChild(actionsCell);
    }

    tbody.appendChild(row);
  }

  table.append(thead, tbody);

  const responsive = document.createElement('div');
  responsive.className = 'table-responsive';
  responsive.appendChild(table);

  section.append(title, responsive);

  return section;
}

// -------------------------
// CONFIGURACIÓ DELS BLOCS
// -------------------------

interface FitxaBlock {
  key: string;
  render: (esdeveniment: Esdeveniment, isAdmin: boolean) => Promise<HTMLElement | null>;
}

interface RunFitxaBlocksOptions {
  containerSelector: string;
  esdeveniment: Esdeveniment;
}

const fitxaBlocks: FitxaBlock[] = [
  {
    key: 'persones',
    render: renderPersones,
  },
  {
    key: 'organitzacions',
    render: renderOrganitzacions,
  },
];

// -------------------------
// EXECUCIÓ DELS BLOCS
// -------------------------

export async function runFitxaEsdevenimentBlocks(options: RunFitxaBlocksOptions): Promise<void> {
  const container = document.querySelector(options.containerSelector);

  if (!container) return;

  container.innerHTML = '';

  const isAdmin = await getIsAdmin();

  for (const block of fitxaBlocks) {
    const element = await block.render(options.esdeveniment, isAdmin);

    if (!element) continue;

    container.appendChild(element);
  }
}
