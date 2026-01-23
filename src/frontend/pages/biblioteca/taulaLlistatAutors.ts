import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
// import { formatData } from '../../utils/formataData';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { Persona } from '../../types/Persona';
import { TaulaDinamica } from '../../types/TaulaDinamica';

export async function taulaLlistatAutors() {
  const isAdmin = await getIsAdmin(); // Comprovar si és admin
  let gestioUrl: string = '';

  if (isAdmin) {
    gestioUrl = '/gestio';
  }

  const columns: TaulaDinamica<Persona>[] = [
    {
      header: 'Autor/a',
      field: 'id',
      render: (_: unknown, row: Persona) => `<a href="https://${window.location.host}${gestioUrl}/biblioteca/fitxa-autor/${row.slug}">${row.AutNom} ${row.AutCognom1}</a>`,
    },
    { header: 'País', field: 'country' },
    { header: 'Professió', field: 'grup' },
    {
      header: 'Dates',
      field: 'yearDie',
      render: (_: unknown, row: Persona) => {
        return `${!row.yearDie ? row.yearBorn : `${row.yearBorn} - ${row.yearDie}`}`;
      },
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Persona) => `
        <a href="https://${window.location.host}/gestio/base-dades-persones/modifica-persona/${row.slug}">
           <button type="button" class="button btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `https://${window.location.host}/api/biblioteca/get/?type=totsAutors`,
    containerId: 'taulaLlistatAutors',
    columns,
    filterKeys: ['AutCognom1'],
    filterByField: 'grup',

    // ✅ NOMÉS aquí
    filterSplitBy: { grup: ',' },
    filterSplitTrim: true,
  });
}
