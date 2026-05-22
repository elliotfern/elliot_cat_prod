import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Viatge } from '../../types/Viatge';

export async function taulaLlistatViatges() {
  const isAdmin = await getIsAdmin(); // Comprovar si és admin

  const columns: TaulaDinamica<Viatge>[] = [
    {
      header: 'Viatge',
      field: 'viatge',
      render: (_: unknown, row: Viatge) => `<a href="https://elliot.cat/gestio/viatges/fitxa-viatge/${row.slug}">${row.viatge}</a>`,
    },
    { header: 'Descripció', field: 'descripcio' },
    { header: 'País', field: 'pais_ca' },
    {
      header: 'Data',
      field: 'dataInici',
      render: (_: unknown, row: Viatge) => {
        const inici = formatData(row.dataInici);
        const fi = row.dataFi && row.dataFi !== '0' ? formatData(row.dataFi) : 'present';
        return `${inici} - ${fi}`;
      },
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Viatge) => `
        <a href="https://${window.location.host}/gestio/viatges/modifica-viatge/${row.id}">
            <button class="btn-petit">Modifica</button>
        </a>`,
    });
  }

  renderDynamicTable({
    url: `viatges/get/llistatViatges`,
    containerId: 'taulaLlistatViatges',
    columns,
    filterKeys: ['viatge', 'descripcio'],
    filterByField: 'pais_ca',
  });
}
