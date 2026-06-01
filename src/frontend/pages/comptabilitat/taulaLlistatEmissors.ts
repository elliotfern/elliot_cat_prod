import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { mostrar } from '../../utils/renderText';
import { Button } from '../../ui/button';
import { INTRANET_URLS } from '../../utils/IntranetUrls';
import { API_URLS } from '../../utils/apiUrls';

interface Emissor {
  id: string;
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
      render: (_: unknown, row: Emissor) => `<a id="${row.id}" href="${INTRANET_URLS.COMPTABILITAT.EMISSOR_FITXA_ID(row.id)}">${row.nom}</a>`,
    },
    {
      header: 'NIF',
      field: 'nif',
      render: (_: unknown, row: Emissor) => `${row.nif}`,
    },
    {
      header: 'Número IVA',
      field: 'numero_iva',
      render: (_: unknown, row: Emissor) => `${mostrar(row.numero_iva, '-')}`,
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
      render: (_, { id }) => Button.edit('Modificar', INTRANET_URLS.COMPTABILITAT.EMISSOR_MODIFICA_ID(id)),
    });
  }

  renderDynamicTable({
    url: API_URLS.GET.EMISSORS_FACTURES, // Assumint que tens aquest endpoint a constants
    containerId: 'taulaLlistatEmissors',
    columns,
    filterKeys: ['pais_ca'],
  });
}
