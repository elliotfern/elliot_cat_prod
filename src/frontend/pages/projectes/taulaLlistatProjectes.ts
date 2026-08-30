import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { ProjecteDetalls } from '../../types/Projecte';
import { formatData } from '../../utils/formataData';

export async function taulaLlistatProjectes() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<ProjecteDetalls>[] = [
    {
      header: 'Projecte',
      field: 'projecte',
      render: (_: unknown, row: ProjecteDetalls) => {
        const projecte = row.projecte?.trim() ?? '';
        const nomComplet = `<a id="${row.id}" title="Fitxa projecte" href="/gestio/projectes/fitxa-projecte/${row.id}">${projecte}</a>`;

        return nomComplet || '-';
      },
    },
    {
      header: 'Categoria',
      field: 'categoria',
      render: (_: unknown, row: ProjecteDetalls) => {
        switch (row.categoria) {
          case 'professional':
            return '<span class="badge bg-primary">Professional</span>';

          case 'personal':
            return '<span class="badge bg-success">Personal</span>';

          default:
            return '';
        }
      },
    },

    {
      header: 'Estat',
      field: 'estat',
      render: (_: unknown, row: ProjecteDetalls) => {
        switch (row.estat) {
          case 'pendent':
            return '<span class="badge bg-secondary">Pendent</span>';

          case 'en_curs':
            return '<span class="badge bg-primary">En curs</span>';

          case 'finalitzat':
            return '<span class="badge bg-success">Finalitzat</span>';

          case 'arxivat':
            return '<span class="badge bg-dark">Arxivat</span>';

          default:
            return '';
        }
      },
    },

    {
      header: 'Prioritat',
      field: 'prioritat',
      render: (_: unknown, row: ProjecteDetalls) => {
        switch (row.prioritat) {
          case 'baixa':
            return '<span class="badge bg-secondary">Baixa</span>';

          case 'normal':
            return '<span class="badge bg-primary">Normal</span>';

          case 'alta':
            return '<span class="badge bg-warning text-dark">Alta</span>';

          case 'urgent':
            return '<span class="badge bg-danger">Urgent</span>';

          default:
            return '';
        }
      },
    },

    {
      header: 'Dates',
      field: 'data_inici',
      render: (_: unknown, row: ProjecteDetalls) => {
        const startDate = row.data_inici?.trim() ? formatData(row.data_inici) : '';

        const endDate = row.data_fi?.trim() ? formatData(row.data_fi) : '';

        if (!startDate && !endDate) {
          return '-';
        }

        return `${startDate} - ${endDate}`;
      },
    },
    {
      header: 'Client',
      field: 'client_id',
      render: (_: unknown, row: ProjecteDetalls) => {
        const nom = row.nom?.trim() ?? '';
        const cognoms = row.cognoms?.trim() ?? '';
        const empresa = row.empresa?.trim() ?? '';
        const data = `<a id="${row.client_id}" title="Fitxa client" href="/gestio/comptabilitat/fitxa-client/${row.client_id}">${nom} ${cognoms} (${empresa})</a>`;

        return data || '-';
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
    filterKeys: ['projecte'],
    filterByFields: ['estat'],
    filterLabels: {
      status: 'Estat:',
    },
  });
}
