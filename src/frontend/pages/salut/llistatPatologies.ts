import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { API_BASE, INTRANET_WEB } from '../../utils/urls';

type MedicamentItem = {
  id: string;
  medicaments: string;
  dosis: string;
  recepta: boolean;
  quantitat_defecte: string | null;
};

type Patologia = {
  id: string;
  patologia: string;
  genere: string;
  medicaments: MedicamentItem[];
};

function showToast(message: string, variant: 'success' | 'danger') {
  let container = document.getElementById('toastContainer');

  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1080';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `alert alert-${variant} shadow mb-2`;
  toast.textContent = message;

  container.appendChild(toast);

  setTimeout(() => {
    toast.remove();
  }, 4000);
}

async function demanarRecepta(id: string, button: HTMLButtonElement) {
  const originalText = button.textContent ?? 'Demanar recepta';

  button.disabled = true;
  button.textContent = 'Enviant...';

  try {
    const response = await fetch(`${API_BASE}/salut/post/receptaMedicament`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ id }),
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
      throw new Error(result?.message || 'Error enviant la recepta');
    }

    button.textContent = 'Recepta demanada ✓';
    button.classList.remove('btn-primary');
    button.classList.add('btn-success');

    showToast('Email enviat correctament al metge.', 'success');
  } catch (error) {
    console.error('demanarRecepta failed:', error);

    button.disabled = false;
    button.textContent = originalText;

    showToast('No s’ha pogut enviar l’email. Torna-ho a provar.', 'danger');
  }
}

// Es penja a window perquè renderDynamicTable pinta HTML com a string (innerHTML),
// així que el onclick inline necessita una funció accessible globalment.
(window as any).__demanarRecepta = (id: string, button: HTMLButtonElement) => {
  demanarRecepta(id, button);
};

// Cada cel·la llista un <div> per medicament, en el mateix ordre a totes les columnes,
// perquè quedin alineats visualment fila a fila dins de la mateixa cel·la de taula.
function renderMedicamentsCell(row: Patologia): string {
  if (!row.medicaments.length) return '<span class="text-muted">Sense medicaments</span>';

  return row.medicaments.map((m) => `<div>${m.medicaments}</div>`).join('');
}

function renderDosisCell(row: Patologia): string {
  if (!row.medicaments.length) return '-';

  return row.medicaments.map((m) => `<div>${m.dosis ?? '-'}</div>`).join('');
}

function renderReceptaCell(row: Patologia): string {
  if (!row.medicaments.length) return '-';

  return row.medicaments.map((m) => `<div>${m.recepta ? 'Si' : 'No'}</div>`).join('');
}

function renderAccionsCell(row: Patologia): string {
  if (!row.medicaments.length) return '';

  return row.medicaments
    .map(
      (m) => `
        <div class="mb-1">
          <button
            type="button"
            class="btn btn-primary btn-sm"
            onclick="window.__demanarRecepta('${m.id}', this)"
          >
            Demanar recepta
          </button>
        </div>`
    )
    .join('');
}

export async function taulaLlistatPatologies() {
  const columns: TaulaDinamica<Patologia>[] = [
    {
      header: 'Patologia',
      field: 'patologia',
    },
    {
      header: 'Medicaments',
      field: 'medicaments' as any,
      render: (_: unknown, row: Patologia) => renderMedicamentsCell(row),
    },
    {
      header: 'Dosis',
      field: 'medicaments' as any,
      render: (_: unknown, row: Patologia) => renderDosisCell(row),
    },
    {
      header: 'Recepta',
      field: 'medicaments' as any,
      render: (_: unknown, row: Patologia) => renderReceptaCell(row),
    },
    {
      header: 'Accions',
      field: 'medicaments' as any,
      render: (_: unknown, row: Patologia) => renderAccionsCell(row),
    },

    {
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Patologia) => `
            <a href="${INTRANET_WEB}/salut/modifica-patologia/${row.id}">
              <button type="button" class="btn btn-warning btn-sm">Modifica</button>
            </a>`,
    },
  ];

  renderDynamicTable<Patologia>({
    url: `salut/get/llistatPatologies`,
    containerId: 'taulaLlistatPatologies',
    columns,
    filterKeys: ['patologia'],
  });
}
