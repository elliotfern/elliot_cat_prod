import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { SerieTv } from '../../types/SerieTv';
import { TaulaDinamica } from '../../types/TaulaDinamica';

export async function taulaLlistatSeries() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<SerieTv>[] = [
    {
      header: 'Sèrie tv',
      field: 'name',
      render: (_: unknown, row: SerieTv) => `<a href="/gestio/cinema/fitxa-serie/${row.slug}">
                  ${row.name ?? ''}
                </a>`,
    },
    { header: 'Gènere', field: 'genere' },
    { header: 'Any', field: 'startYear' },
    {
      header: 'Director/a',
      field: 'cognoms',
      render: (_: unknown, row: SerieTv) => {
        return ` <a href="/gestio/base-dades-persones/fitxa-persona/${row.slugDirector}">
                  ${row.nom ?? ''} ${row.cognoms ?? ''}
                </a>`;
      },
    },
    { header: 'País', field: 'country' },
    { header: 'Idioma', field: 'lang' },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: SerieTv) => `<a id="${row.id}" title="Sèrie tv" href="/gestio/cinema/modifica-serie/${row.id}"><button type="button" class="button btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `cinema/get/series`,
    containerId: 'taulaLlistatSeries',
    columns,
    filterKeys: ['nom', 'cognoms', 'name'],
    filterByField: 'country',
  });
}
