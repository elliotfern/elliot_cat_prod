import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { SerieTv } from '../../types/SerieTv';
import { TaulaDinamica } from '../../types/TaulaDinamica';

export async function taulaLlistatDirectors() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<SerieTv>[] = [
    {
      header: '',
      field: 'name',
      render: (_: unknown, row: SerieTv) => `<a 
                  id="${row.id}"
                  title="Author page"
                  href="/gestio/base-dades-persones/fitxa-persona/${row.slug}"
                >
                  <img 
                    src="https://media.elliot.cat/img/persona/${row.nameImg}.jpg"
                    style="height:70px"
                  >
                </a>`,
    },

    {
      header: 'Director/a',
      field: 'name',
      render: (_: unknown, row: SerieTv) => ` <a 
                  id="${row.id}"
                  title="Author page"
                  href="/gestio/base-dades-persones/fitxa-persona/${row.slug}"
                >
                  ${row.nom} ${row.cognoms}
                </a>`,
    },
    {
      header: 'Anys',
      field: 'any_defuncio',
      render: (_: unknown, row: SerieTv) => {
        return `${!row.any_defuncio ? row.any_naixement : `${row.any_naixement} - ${row.any_defuncio}`}`;
      },
    },
    { header: 'País', field: 'pais_ca' },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: SerieTv) => `<a id="${row.id}" title="Actor" href="/gestio/base-dades-persones/modifica-persona/${row.slug}"><button type="button" class="button btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `cinema/get/directors`,
    containerId: 'taulaLlistatDirectors',
    columns,
    filterKeys: ['nom', 'cognoms', 'name'],
    filterByField: 'country',
  });
}
