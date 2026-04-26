import { state } from '../../../types/Esdeveniment';
import { getEventos, getSubetapas } from './api';
import { renderFiltros } from './filtres';
import { renderPaginacion } from './paginacio';
import { renderSubetapas } from './renderSubEtapes';
import { pintarTabla, renderTabla } from './taula';

const root = document.getElementById('cronologia')!;

export async function loadEventos() {
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

  container.innerHTML = ''; // limpia

  renderTabla(container);
  pintarTabla();
  renderPaginacion(container); // 👈 luego lo añadimos
}

export async function loadSubetapas() {
  const container = document.getElementById('subetapas-container');
  if (!container) return;

  container.innerHTML = 'Carregant...';

  const data = await getSubetapas(state.etapa);

  if (data.error) {
    container.innerHTML = '<p>Error carregant subetapes</p>';
    return;
  }

  renderSubetapas(container, data);
}

export function initCronologia() {
  renderFiltros(root);

  const tableContainer = document.createElement('div');
  tableContainer.id = 'table-container';
  root.appendChild(tableContainer);
}
