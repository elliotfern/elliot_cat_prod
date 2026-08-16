import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { API_BASE } from '../../utils/urls';

type Medicament = {
  id: string;
  patologia: string;
  medicaments: string;
  dosis: string;
  recepta: string;
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

export async function taulaLlistatMedicaments() {
  const columns: TaulaDinamica<Medicament>[] = [
    {
      header: 'Patologia',
      field: 'patologia',
    },
    {
      header: 'Medicaments',
      field: 'medicaments',
    },
    {
      header: 'Dosis',
      field: 'dosis',
    },
    {
      header: 'Recepta',
      field: 'recepta',
    },
    {
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Medicament) => `
        <button
          type="button"
          class="btn btn-primary btn-sm"
          onclick="window.__demanarRecepta('${row.id}', this)"
        >
          Demanar recepta
        </button>`,
    },
  ];

  renderDynamicTable<Medicament>({
    url: `salut/get/llistatMedicaments`,
    containerId: 'taulaLlistatMedicaments',
    columns,
    filterKeys: ['patologia', 'medicaments'],
  });
}
