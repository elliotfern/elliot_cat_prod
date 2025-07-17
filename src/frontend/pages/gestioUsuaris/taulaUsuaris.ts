import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Usuari } from '../../types/Usuari';

export async function taulaUsuaris() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Usuari>[] = [
    {
      header: 'Nom i cognoms',
      field: 'nom',
      render: (_: unknown, row: Usuari) => `${row.nom} ${row.cognom}`,
    },
    { header: 'Email', field: 'email' },
    { header: 'Tipus', field: 'tipus' },
    {
      header: 'Data alta',
      field: 'dateCreated',
      render: (_: unknown, row: Usuari) => {
        const inici = formatData(row.dateCreated);
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

  renderDynamicTable({
    url: `https://api.elliot.cat/api/users`,
    containerId: 'taulaUsuaris',
    columns,
    filterKeys: ['nom', 'cognom'],
    filterByField: 'tipus',
  });
}
