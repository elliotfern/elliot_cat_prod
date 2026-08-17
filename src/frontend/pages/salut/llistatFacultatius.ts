import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { INTRANET_WEB } from '../../utils/urls';

type Facultatiu = {
  id: string;
  nom: string;
  direccio: string | null;
  ciutat_id: string | null;
  nomCiutat: string | null;
  email: string | null;
  telefon: string | null;
  especialitat: string | null;
};

export async function taulaLlistatFacultatius() {
  const columns: TaulaDinamica<Facultatiu>[] = [
    {
      header: 'Nom',
      field: 'nom',
    },
    {
      header: 'Direcció',
      field: 'direccio',
    },
    {
      header: 'Especialitat',
      field: 'especialitat',
    },
    {
      header: 'Ciutat',
      field: 'nomCiutat',
    },
    {
      header: 'Email',
      field: 'email',
      render: (_: unknown, row: Facultatiu) => (row.email ? `<a href="mailto:${row.email}">${row.email}</a>` : ''),
    },
    {
      header: 'Telèfon',
      field: 'telefon',
    },
    {
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Facultatiu) => `
        <a href="${INTRANET_WEB}/salut/modifica-facultatiu/${row.id}">
          <button type="button" class="button btn-petit">Modifica</button>
        </a>`,
    },
  ];

  renderDynamicTable<Facultatiu>({
    url: `salut/get/llistatFacultatius`,
    containerId: 'taulaLlistatFacultatius',
    columns,
    filterKeys: ['nom', 'nomCiutat'],
  });
}
