import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Imatge } from '../../types/Imatge';
import { api } from '../../core/api/client';

// ============================================================
// ESTADO
// ============================================================

let listenerEliminacioInicialitzat = false;
let eliminantImatge = false;

// ============================================================
// TAULA LLISTAT IMATGES
// ============================================================

export async function taulaLlistatImatges() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Imatge>[] = [
    {
      header: 'Imatge',
      field: 'nom',
      render: (_: unknown, row: Imatge) =>
        `<a
          id="${row.id}"
          title="Imatges detalls"
          href="/gestio/auxiliars/fitxa-imatge/${row.id}"
        >
          ${row.nom}
        </a>`,
    },

    {
      header: '',
      field: 'nameImg',
      render: (_: unknown, row: Imatge) =>
        `<a
          id="${row.id}"
          title="Imatges detalls"
          href="/gestio/auxiliars/fitxa-imatge/${row.id}"
        >
          <img
            src="https://media.elliot.cat/img/${row.name}/${row.nameImg}.jpg"
            alt="${escapeHtmlAttribute(row.nom)}"
            width="60"
            height="auto"
          >
        </a>`,
    },

    {
      header: 'ID',
      field: 'id',
      render: (_: unknown, row: Imatge) => `[img id=${row.id}] - [img id=${row.id} alt="${row.nom}" caption="${row.alt}"]`,
    },

    {
      header: 'Tipus Imatge',
      field: 'name',
    },

    {
      header: 'Data creació',
      field: 'dateCreated',
      render: (_: unknown, row: Imatge) => {
        return `${formatData(row.dateCreated)}`;
      },
    },
  ];

  // ==========================================================
  // ACCIONS ADMIN
  // ==========================================================

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',

      render: (_: unknown, row: Imatge) => `
        <div class="d-flex flex-column gap-1">

          <a
            href="/gestio/auxiliars/modifica-imatge/${row.id}"
            title="Modifica"
            class="btn btn-warning btn-sm"
          >
            Modifica
          </a>

          <button
            type="button"
            class="btn btn-danger btn-sm btn-elimina-imatge"
            data-id="${row.id}"
            data-nom="${escapeHtmlAttribute(row.nom)}"
          >
            Elimina
          </button>

        </div>
      `,
    });
  }

  // ==========================================================
  // RENDERIZAR TAULA
  // ==========================================================

  renderDynamicTable({
    url: 'auxiliars/get/llistatCompletImatges',
    containerId: 'taulaLlistatImatges',
    columns,
    filterKeys: ['nom'],
    filterByField: 'name',
  });

  // ==========================================================
  // INICIALIZAR EVENTO ELIMINAR
  // ==========================================================

  inicialitzarEliminacioImatges();
}

// ============================================================
// INICIALITZAR ELIMINACIÓ IMATGES
// ============================================================

function inicialitzarEliminacioImatges(): void {
  // Evitar registrar el listener varias veces
  if (listenerEliminacioInicialitzat) {
    return;
  }

  const container = document.getElementById('taulaLlistatImatges');

  if (!container) {
    return;
  }

  listenerEliminacioInicialitzat = true;

  // ==========================================================
  // EVENT DELEGATION
  // ==========================================================

  container.addEventListener('click', async (event) => {
    const target = event.target as HTMLElement;

    const button = target.closest('.btn-elimina-imatge') as HTMLButtonElement | null;

    if (!button) {
      return;
    }

    // ========================================================
    // EVITAR COMPORTAMENT PER DEFECTE
    // ========================================================

    event.preventDefault();

    // ========================================================
    // EVITAR PETICIONS DUPLICADES
    // ========================================================

    if (eliminantImatge) {
      return;
    }

    // ========================================================
    // ID
    // ========================================================

    const id = button.dataset.id;

    if (!id) {
      return;
    }

    // ========================================================
    // NOMBRE
    // ========================================================

    const nom = button.dataset.nom ?? '';

    // ========================================================
    // CONFIRMACIÓN
    // ========================================================

    const confirmar = window.confirm(`Estàs segur que vols eliminar la imatge "${nom}"?`);

    if (!confirmar) {
      return;
    }

    // ========================================================
    // BLOQUEAR ELIMINACIÓN
    // ========================================================

    eliminantImatge = true;

    button.disabled = true;

    try {
      await api.delete('auxiliars/imatges/delete/imatgeId', { id });

      // Recargar tabla
      await taulaLlistatImatges();

      // Mostrar mensaje después de recargar
      mostrarMissatgeImatge("La imatge s'ha eliminat correctament.", 'success');
    } catch (error) {
      console.error('Error eliminant la imatge:', error);

      // ======================================================
      // ERROR
      // ======================================================

      mostrarMissatgeImatge("No s'ha pogut eliminar la imatge.", 'danger');
    } finally {
      eliminantImatge = false;
    }
  });
}

// ============================================================
// MOSTRAR MENSAJE BOOTSTRAP
// ============================================================
function mostrarMissatgeImatge(mensaje: string, tipus: 'success' | 'danger'): void {
  const container = document.getElementById('avis-alert');

  if (!container) {
    return;
  }

  const alert = document.createElement('div');

  alert.className = `alert alert-${tipus} alert-dismissible fade show`;
  alert.setAttribute('role', 'alert');

  alert.innerHTML = `
    ${mensaje}

    <button
      type="button"
      class="btn-close"
      data-bs-dismiss="alert"
      aria-label="Tancar">
    </button>
  `;

  container.prepend(alert);

  setTimeout(() => {
    alert.classList.remove('show');

    setTimeout(() => {
      alert.remove();
    }, 150);
  }, 5000);
}

// ============================================================
// ESCAPAR HTML
// ============================================================

function escapeHtml(value: string): string {
  return value.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

// ============================================================
// ESCAPAR ATRIBUTO HTML
// ============================================================

function escapeHtmlAttribute(value: string): string {
  return escapeHtml(value);
}
