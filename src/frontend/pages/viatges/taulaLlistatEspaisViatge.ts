import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Espai } from '../../types/Espai';

export async function taulaLlistatEspaisViatges(slug: string) {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Espai>[] = [
    {
      header: 'Espai',
      field: 'nom',
      render: (_: unknown, row: Espai) => `<a href="https://elliot.cat/gestio/viatges/fitxa-espai/${row.slug}">${row.nom}</a>`,
    },
    { header: 'Ciutat', field: 'ciutat' },
    {
      header: 'Data visita',
      field: 'dataVisita',
      render: (_: unknown, row: Espai) => {
        const inici = formatData(row.dataVisita);
        return `${inici}`;
      },
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Espai) => `
         <a href="https://elliot.cat/gestio/viatges/modifica-espai/${row.id}"><button class="btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `viatges/get/llistatEspaisViatge?viatge=${slug}`,
    containerId: 'taulaLlistatEspaisViatge',
    columns,
    filterKeys: ['nom', 'ciutat'],
    filterByField: 'ciutat',
  });
}
