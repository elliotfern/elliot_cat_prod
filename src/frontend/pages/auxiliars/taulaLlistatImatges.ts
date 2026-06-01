import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Imatge } from '../../types/Imatge';

export async function taulaLlistatImatges() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Imatge>[] = [
    {
      header: 'Imatge',
      field: 'nom',
      render: (_: unknown, row: Imatge) => `<a id="${row.id}" title="Imatges detalls" href="/gestio/auxiliars/fitxa-imatge/${row.id}">${row.nom}</a>`,
    },

    {
      header: '',
      field: 'nameImg',
      render: (_: unknown, row: Imatge) => `<a id="${row.id}" title="Imatges detalls" href="/gestio/auxiliars/fitxa-imatge/${row.id}"> <img src="https://media.elliot.cat/img/${row.name}/${row.nameImg}.jpg" alt="${row.nom}" width="60" height="auto"> </a>`,
    },

    {
      header: 'ID',
      field: 'id',
      render: (_: unknown, row: Imatge) => `[img id=${row.id}] - [img id=${row.id} alt="${row.nom}" caption="${row.alt}"]`,
    },

    { header: 'Tipus Imatge', field: 'name' },
    {
      header: 'Data creació',
      field: 'dateCreated',
      render: (_: unknown, row: Imatge) => {
        return `${formatData(row.dateCreated)}`;
      },
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Imatge) => `<a a id="${row.id}" title="Modifica" href=/gestio/auxiliars/modifica-imatge/${row.id}"><button class="btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `/auxiliars/get/llistatCompletImatges`,
    containerId: 'taulaLlistatImatges',
    columns,
    filterKeys: ['nom'],
    filterByField: 'name',
  });
}
