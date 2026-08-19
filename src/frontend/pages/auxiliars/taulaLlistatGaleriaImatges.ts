import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';

interface GaleriaImatges {
  id: string;
  nom: string;
  directori: string;
  alt: string | null;
  dateCreated: string | null;
  dateModified: string | null;
}

export async function taulaLlistatGaleriaImatges() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<GaleriaImatges>[] = [
    // ============================================================
    // NOMBRE
    // ============================================================

    {
      header: 'Galeria',
      field: 'nom',
      render: (_: unknown, row: GaleriaImatges) =>
        `<a
          id="${row.id}"
          title="Detalls galeria"
          href="/gestio/auxiliars/fitxa-galeria-imatges/${row.id}">
          ${row.nom}
        </a>`,
    },

    // ============================================================
    // DIRECTORIO
    // ============================================================

    {
      header: 'Directori',
      field: 'directori',
    },

    // ============================================================
    // ALT
    // ============================================================

    {
      header: 'Descripció',
      field: 'alt',
      render: (_: unknown, row: GaleriaImatges) => row.alt ?? '',
    },

    // ============================================================
    // DATA CREACIÓ
    // ============================================================

    {
      header: 'Data creació',
      field: 'dateCreated',
      render: (_: unknown, row: GaleriaImatges) => {
        return row.dateCreated ? formatData(row.dateCreated) : '';
      },
    },

    // ============================================================
    // DATA MODIFICACIÓ
    // ============================================================

    {
      header: 'Data modificació',
      field: 'dateModified',
      render: (_: unknown, row: GaleriaImatges) => {
        return row.dateModified ? formatData(row.dateModified) : '';
      },
    },
  ];

  // ============================================================
  // ACCIONS
  // ============================================================

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: GaleriaImatges) =>
        `<a
          id="${row.id}"
          title="Modifica"
          href="/gestio/auxiliars/modifica-galeria-imatges/${row.id}">
          <button
            type="button"
            class="btn btn-warning btn-sm">
            Modifica
          </button>
        </a>`,
    });
  }

  // ============================================================
  // TAULA
  // ============================================================

  renderDynamicTable({
    url: 'auxiliars/imatges/get/galeriaImatges',
    containerId: 'taulaLlistatGaleriesImatges',
    columns,
    filterKeys: ['nom', 'directori', 'alt'],
    filterByField: 'nom',
  });
}
