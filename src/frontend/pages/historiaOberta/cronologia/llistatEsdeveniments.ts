import { getIsAdmin } from '../../../services/auth/isAdmin';
import { state } from '../../../types/Esdeveniment';
import { getEventos, getSubetapas } from './api';
import { renderFiltros, setActiveEtapaUI } from './filtres';
import { renderPaginacion } from './paginacio';
import { renderSubetapas } from './renderSubEtapes';
import { renderTabla } from './taula';

const root = document.getElementById('cronologia')!;

export async function loadEventos(): Promise<void> {
  const container = document.getElementById('table-container')!;

  container.innerHTML = 'Carregant...';

  const data = await getEventos(state.etapa, state.subetapa || undefined);

  if (!data.data || data.data.length === 0) {
    container.innerHTML = `
      <div class="alert alert-info">
        No hi ha cap esdeveniment
      </div>
    `;
    return;
  }

  state.eventos = data.data;
  state.paginaActual = 1;

  container.innerHTML = '';

  const esAdmin = await getIsAdmin();
  renderTabla(container, esAdmin);

  renderPaginacion(container);

  setActiveEtapaUI();
}

export async function loadSubetapas(): Promise<void> {
  const container = document.getElementById('subetapas-container');
  if (!container) return;

  container.innerHTML = 'Carregant...';

  const data = await getSubetapas(state.etapa);

  if (data.error) {
    container.innerHTML = '<p>Error carregant subetapes</p>';
    return;
  }

  renderSubetapas(container, data.data);
}

export function initCronologia(): void {
  renderFiltros(root);

  const tableContainer = document.createElement('div');
  tableContainer.id = 'table-container';
  root.appendChild(tableContainer);
}
