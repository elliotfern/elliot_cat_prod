import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';

export async function taulaLlistatPelicules() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Pelicula>[] = [
    {
      header: 'Pel·lícula',
      field: 'pelicula',
      render: (_: unknown, row: Pelicula) => `<a id="${row.id}" title="Show movie details" href="/gestio/cinema/fitxa-pelicula/${row.slug}">${row.pelicula}</a>`,
    },
    { header: 'Any', field: 'any' },
    {
      header: 'Director/a',
      field: 'cognoms',
      render: (_: unknown, row: Pelicula) => {
        return `<a id="${row.id}" title="Fitxa director" href="/gestio/base-dades-persones/fitxa-persona/${row.director_slug}">${row.nom} ${row.cognoms}</a>`;
      },
    },
    { header: 'País', field: 'pais_ca' },
    { header: 'Gènere', field: 'genere' },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Pelicula) => `<a id="${row.id}" title="Show movie details" href="/gestio/cinema/modifica-pelicula/${row.id}"><button type="button" class="button btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `/cinema/get/pelicules`,
    containerId: 'taulaLlistatPelicules',
    columns,
    filterKeys: ['nom', 'cognoms', 'pelicula'],
    filterByField: 'pais_ca',
  });
}
