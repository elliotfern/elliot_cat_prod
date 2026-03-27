import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { API_URLS } from '../../utils/apiUrls';

interface Emissor {
  id: number;
  nom: string;
  nif: string;
  numero_iva: string;
  pais_ca: string;
  adreca: string;
  telefon: string;
  email: string;
  created_at: string;
  updated_at: string;
}

const url = window.location.href;
const pageType = getPageType(url);

export async function taulaLlistatEmissors() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Emissor>[] = [
    {
      header: 'Nom',
      field: 'nom',
      render: (_: unknown, row: Emissor) => `<a id="${row.id}" href="https://${window.location.hostname}/gestio/comptabilitat/fitxa-emissor/${row.id}">${row.nom}</a>`,
    },
    {
      header: 'NIF',
      field: 'nif',
      render: (_: unknown, row: Emissor) => `${row.nif}`,
    },
    {
      header: 'Número IVA',
      field: 'numero_iva',
      render: (_: unknown, row: Emissor) => `${row.numero_iva}`,
    },
    {
      header: 'País',
      field: 'pais_ca',
      render: (_: unknown, row: Emissor) => `${row.pais_ca}`,
    },
    {
      header: 'Telèfon',
      field: 'telefon',
      render: (_: unknown, row: Emissor) => `${row.telefon}`,
    },
    {
      header: 'Email',
      field: 'email',
      render: (_: unknown, row: Emissor) => `${row.email}`,
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Emissor) => `<a id="${row.id}" title="Modifica" href="https://${window.location.hostname}/gestio/comptabilitat/modifica-emissor/${row.id}"><button class="btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: API_URLS.GET.EMISSORS_FACTURES, // Assumint que tens aquest endpoint a constants
    containerId: 'taulaLlistatEmissors',
    columns,
    filterKeys: ['pais_ca'],
  });
}
