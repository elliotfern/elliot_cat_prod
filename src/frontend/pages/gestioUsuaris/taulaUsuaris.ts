import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { registerDeleteCallback, initDeleteHandlers } from '../../components/renderTaula/handleDelete';
import { formatData } from '../../utils/formataData';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Usuari } from '../../types/Usuari';

export async function taulaUsuaris() {
  const isAdmin = await getIsAdmin();
  const reloadKey = 'reload-taula-usuaris';

  const columns: TaulaDinamica<Usuari>[] = [
    {
      header: 'Nom i cognoms',
      field: 'name',
      render: (_: unknown, row: Usuari) => `${row.name}`,
    },
    { header: 'Email', field: 'email' },
    { header: 'Tipus', field: 'type' },
    {
      header: 'Data alta',
      field: 'createdAt',
      render: (_: unknown, row: Usuari) => {
        const inici = formatData(row.createdAt);
        return `${inici}`;
      },
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Usuari) => `
         <a href="https://${window.location.host}/gestio/gestio-usuaris/modifica-usuari/${row.id}"><button class="btn-petit">Modifica</button></a>`,
    });
  }

  if (isAdmin) {
    columns.push({
      header: '',
      field: 'id',
      render: (_: unknown, row: Usuari) => `
    <button 
      type="button"
      class="btn-petit"
      data-id="${row.id}" 
      data-url="https://api.elliot.cat/api/users/${row.id}"
      data-reload-callback="${reloadKey}"
    >
      Elimina
    </button>`,
    });
  }

  renderDynamicTable({
    url: `https://api.elliot.cat/api/users`,
    containerId: 'taulaUsuaris',
    columns,
    filterKeys: ['name'],
    filterByField: 'type',
  });

  // Registra el callback con una clave única
  registerDeleteCallback(reloadKey, () => taulaUsuaris());

  // Inicia el listener una sola vez
  initDeleteHandlers();
}
