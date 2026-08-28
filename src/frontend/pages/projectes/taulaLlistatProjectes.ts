import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { ProjecteDetalls } from '../../types/Projecte';

function labelStatus(status: number): string {
  switch (status) {
    case 0:
      return 'Arxivat';
    case 1:
      return 'Actiu';
    case 2:
      return 'En pausa';
    case 3:
      return 'Finalitzat';
    default:
      return String(status);
  }
}

function escapeHtml(s: string): string {
  return s.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
}

export async function taulaLlistatProjectes() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<ProjecteDetalls>[] = [
    {
      header: 'Projecte',
      field: 'name',
      render: (_: unknown, row: ProjecteDetalls) => {
        const name = row.name?.trim() ?? '';
        const nomComplet = `${name}`.trim();

        return nomComplet || '-';
      },
    },
    {
      header: 'Categoria',
      field: 'nomCategoria',
    },
    {
      header: 'Dates',
      field: 'start_date',
      render: (_: unknown, row: ProjecteDetalls) => {
        const startDate = row.start_date?.trim() ?? '';
        const endDate = row.end_date?.trim() ?? '';
        const data = `${startDate} - ${endDate}`.trim();

        return data || '-';
      },
    },
    {
      header: 'Client',
      field: 'client_id',
    },
    {
      header: 'Estat',
      field: 'status',
      render: (_: unknown, row: ProjecteDetalls) => {
        return labelStatus(Number(row.status));
      },
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: ProjecteDetalls) =>
        `<a id="${row.id}" title="Modifica" href="/gestio/projectes/modifica-projecte/${row.id}">
          <button type="button" class="btn btn-warning btn-sm">Modifica</button>
        </a>`,
    });
  }

  renderDynamicTable({
    url: `projectes/get/llistatProjectes`,
    containerId: 'taulaLlistatProjectes',
    columns,
    filterKeys: ['name'],
    filterByFields: ['status'],
    filterLabels: {
      status: 'Estat:',
    },
  });
}
