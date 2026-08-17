import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { INTRANET_WEB } from '../../utils/urls';

type Facultatiu = {
  id: string;
  nom: string;
  medicament: string | null;
  dosis: string | null;
  necessita_recepta: string | null;
  email: string | null;
  telefon: string | null;
  especialitat: string | null;
};

export async function taulaLlistatMedicaments() {
  const columns: TaulaDinamica<Facultatiu>[] = [
    {
      header: 'Nom',
      field: 'medicament',
    },
    {
      header: 'Dosis',
      field: 'dosis',
    },
    {
      header: 'Recepta',
      field: 'necessita_recepta',
      render: (value: unknown) => (Number(value) === 1 ? 'Si' : 'No'),
    },
    {
      header: 'Facultatiu',
      field: 'nom',
    },

    {
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Facultatiu) => `
        <a href="${INTRANET_WEB}/salut/modifica-medicament/${row.id}">
          <button type="button" class="button btn-petit">Modifica</button>
        </a>`,
    },
  ];

  renderDynamicTable<Facultatiu>({
    url: `salut/get/llistatMedicaments`,
    containerId: 'taulaLlistatMedicaments',
    columns,
    filterKeys: ['medicament'],
  });
}
