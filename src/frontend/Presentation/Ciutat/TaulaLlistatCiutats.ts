import { taulaDinamica } from '../Components/Table/TaulaRender';

import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Button } from '../Components/Button/Button';

import { registerDeleteCallback, initDeleteHandlers, registerDeleteHandler } from '../Components/Table/handleDelete';

import { CiutatApiRepository } from '../../Infrastructure/Api/Ciutat/CiutatApiRepository';
import { GetCiutats } from '../../Application/Ciutat/List/GetCiutats';
import { DeleteCiutat } from '../../Application/Ciutat/Delete/DeleteCiutat';
import { Ciutat } from '../../Domain/Ciutat/Entity/Ciutat';
import { formatData } from '../../utils/formataData';

export async function taulaLlistatCiutats(): Promise<void> {
  const isAdmin = await getIsAdmin();

  const repository = new CiutatApiRepository();
  const getCiutats = new GetCiutats(repository);
  const deleteCiutat = new DeleteCiutat(repository);

  let ciutats: Ciutat[];

  try {
    ciutats = await getCiutats.execute();
  } catch (error) {
    console.error(error);
    return;
  }

  const editHref = (id: string): string => `/gestio/auxiliars/modifica-ciutat/${id}`;
  const fitxaHref = (id: string): string => `/gestio/auxiliars/fitxa-ciutat/${id}`;
  const fitxaPaisHref = (id: string): string => `/gestio/auxiliars/fitxa-pais/${id}`;
  const reloadKey = 'reload-taula-ciutats';

  const columns: TaulaDinamica<Ciutat>[] = [
    {
      header: 'Ciutat',
      field: 'ciutat',
      render: (_: unknown, row: Ciutat) => `<a id="ciutat-${row.id}" href="${fitxaHref(row.id)}">${row.ciutat}</a>`,
    },

    {
      header: 'País',
      field: 'pais',
      render: (_: unknown, row: Ciutat) => `<a id="pais-${row.pais.id}" href="${fitxaPaisHref(row.pais.id)}">${row.pais.pais}</a>`,
    },

    {
      header: 'Última actualització',
      field: 'updated_at',
      render: (_: unknown, row: Ciutat) => `${formatData(row.updated_at)}`,
    },
  ];

  if (isAdmin) {
    columns.push(
      {
        header: '',
        field: 'id',
        render: (_: unknown, row: Ciutat) => Button.edit('Modifica', editHref(row.id)),
      },

      {
        header: '',
        field: 'id',
        render: (_: unknown, row: Ciutat) => Button.delete('Elimina', row.id, 'ciutat', reloadKey),
      }
    );
  }

  taulaDinamica({
    data: ciutats,
    containerId: 'taulaLlistatCiutats',
    columns,
    filterKeys: ['ciutat'],
    filterByField: 'pais.pais',
    filterLabels: {
      'pais.pais': 'País:',
    },
  });

  registerDeleteHandler('ciutat', (id) => deleteCiutat.execute(id));

  registerDeleteCallback(reloadKey, () => taulaLlistatCiutats());

  initDeleteHandlers();
}
