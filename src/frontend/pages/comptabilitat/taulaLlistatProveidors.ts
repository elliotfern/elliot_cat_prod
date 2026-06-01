import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Button } from '../../ui/button';
import { API_URLS } from '../../utils/apiUrls';
import { INTRANET_URLS } from '../../utils/IntranetUrls';

export interface Proveidor {
  id: string;
  nom: string;
  nif?: string;
  adreca?: string;
  ciutat?: string;
  codi_postal?: string;
  pais?: string;
  telefon?: string;
  email?: string;
  web?: string;
  contacte?: string;
  notes?: string;
  created_at?: string;
  updated_at?: string;
}

export async function taulaProveidors() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Proveidor>[] = [
    {
      header: 'Nom',
      field: 'nom',
      render: (_: unknown, row: Proveidor) =>
        `<a href="${INTRANET_URLS.COMPTABILITAT.PROVEIDOR_FITXA_ID(row.id)}">
           ${row.nom}
         </a>`,
    },
    { header: 'NIF', field: 'nif' },
    { header: 'Adreça', field: 'adreca' },
    { header: 'Ciutat', field: 'ciutat' },
    { header: 'País', field: 'pais' },
    { header: 'Web', field: 'web' },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_, { id }) => Button.edit('Modificar', INTRANET_URLS.COMPTABILITAT.PROVEIDOR_MODIFICA_ID(id)),
    });
  }

  renderDynamicTable({
    url: API_URLS.GET.PROVEIDORS,
    containerId: 'taulaLlistatProveidors',
    columns,
    filterKeys: ['nom', 'ciutat', 'pais', 'nif', 'contacte'],
    filterByField: 'pais',
  });
}
