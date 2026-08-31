import { GetPaisos } from '../../Application/Pais/List/GetPaisos';
import { Pais } from '../../Domain/Pais/Entity/Pais';
import { PaisApiRepository } from '../../Infrastructure/Api/Pais/PaisApiRepository';
import { taulaDinamica } from '../Components/Table/TaulaRender';

import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Button } from '../Components/Button/Button';

import { registerDeleteCallback, initDeleteHandlers, registerDeleteHandler } from '../Components/Table/handleDelete';
import { DeletePais } from '../../Application/Pais/Delete/DeletePais';

export async function taulaLlistatPaisos(): Promise<void> {
  const isAdmin = await getIsAdmin();

  const repository = new PaisApiRepository();
  const getPaisos = new GetPaisos(repository);
  const deletePais = new DeletePais(repository);

  let paisos: Pais[];

  try {
    paisos = await getPaisos.execute();
  } catch (error) {
    console.error(error);
    return;
  }

  const editHref = (id: string): string => `/gestio/auxiliars/modifica-pais/${id}`;
  const fitxaHref = (id: string): string => `/gestio/auxiliars/fitxa-pais/${id}`;
  const reloadKey = 'reload-taula-paisos';

  const columns: TaulaDinamica<Pais>[] = [
    {
      header: 'País',
      field: 'pais',
      render: (_: unknown, row: Pais) => `<a id="pais-${row.id}" href="${fitxaHref(row.id)}">${row.pais}</a>`,
    },
  ];

  if (isAdmin) {
    columns.push(
      {
        header: '',
        field: 'id',
        render: (_: unknown, row: Pais) => Button.edit('Modifica', editHref(row.id)),
      },

      {
        header: '',
        field: 'id',
        render: (_: unknown, row: Pais) => Button.delete('Elimina', row.id, 'pais', reloadKey),
      }
    );
  }

  taulaDinamica({
    data: paisos,
    containerId: 'taulaLlistatPaisos',
    columns,
  });

  registerDeleteHandler('pais', (id) => deletePais.execute(id));

  registerDeleteCallback(reloadKey, () => taulaLlistatPaisos());

  initDeleteHandlers();
}
