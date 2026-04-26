import { state } from '../../../types/Esdeveniment';
import { getEventos, getSubetapas } from './api';
import { renderFiltros } from './filtes';
import { pintarTabla, renderTabla } from './taula';

const root = document.getElementById('cronologia')!;

export async function loadEventos() {
  const data = await getEventos(state.etapa, state.subetapa || undefined);

  state.eventos = data.data || [];
  state.paginaActual = 1;

  pintarTabla();
}

export async function loadSubetapas() {
  const data = await getSubetapas(state.etapa);
  console.log(data); // aquí renderizarías botones
}

export function initCronologia() {
  renderFiltros(root);

  const tableContainer = document.createElement('div');
  root.appendChild(tableContainer);

  renderTabla(tableContainer);

  loadEventos();
}
