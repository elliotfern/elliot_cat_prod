import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { API_URLS } from '../../utils/apiUrls';
import { DOMAIN_WEB } from '../../utils/urls';

export interface Proveidor {
  id: number;
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
        `<a href="${DOMAIN_WEB}/gestio/comptabilitat/fitxa-proveidor/${row.id}">
           ${row.nom}
         </a>`,
    },
    { header: 'NIF', field: 'nif' },
    { header: 'Adreça', field: 'adreca' },
    { header: 'Ciutat', field: 'ciutat' },
    { header: 'Codi Postal', field: 'codi_postal' },
    { header: 'País', field: 'pais' },
    { header: 'Telèfon', field: 'telefon' },
    { header: 'Email', field: 'email' },
    { header: 'Web', field: 'web' },
    { header: 'Contacte', field: 'contacte' },
    { header: 'Notes', field: 'notes' },
    {
      header: 'Creat',
      field: 'created_at',
      render: (_: unknown, row: Proveidor) => (row.created_at ? new Date(row.created_at).toLocaleDateString() : ''),
    },
    {
      header: 'Actualitzat',
      field: 'updated_at',
      render: (_: unknown, row: Proveidor) => (row.updated_at ? new Date(row.updated_at).toLocaleDateString() : ''),
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Proveidor) => `
        <a href="${DOMAIN_WEB}/gestio/comptabilitat/modifica-proveidor/${row.id}">
          <button class="btn-petit">Modifica</button>
        </a>`,
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
