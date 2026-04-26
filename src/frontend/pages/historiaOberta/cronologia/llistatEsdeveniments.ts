import { state } from '../../../types/Esdeveniment';
import { getEventos, getSubetapas } from './api';
import { renderFiltros } from './filtres';
import { renderSubetapas } from './renderSubEtapes';
import { pintarTabla, renderTabla } from './taula';

const root = document.getElementById('cronologia')!;

export async function loadEventos() {
  const data = await getEventos(state.etapa, state.subetapa || undefined);

  state.eventos = data.data || [];
  state.paginaActual = 1;

  pintarTabla();
}

export async function loadSubetapas() {
  const container = document.getElementById("subetapas-container");
  if (!container) return;

  container.innerHTML = "Carregant...";

  const data = await getSubetapas(state.etapa);

  if (data.error) {
    container.innerHTML = "<p>Error carregant subetapes</p>";
    return;
  }

  renderSubetapas(container, data);
}

export function initCronologia() {
  renderFiltros(root);

  const tableContainer = document.createElement('div');
  root.appendChild(tableContainer);

  renderTabla(tableContainer);

  loadEventos();
}
