import { state } from '../../../types/Esdeveniment';
import { loadEventos, loadSubetapas } from './llistatEsdeveniments';
import { getEtapas } from './api';

interface Etapa {
  id: string;
  etapaNom: string;
}

export async function renderFiltros(container: HTMLElement): Promise<void> {
  const wrapper = document.createElement('div');

  wrapper.innerHTML = `
    <h4>Selecciona una Etapa Històrica:</h4>
    <div class="d-flex flex-wrap gap-2 mb-3" id="etapas"></div>
    <div id="subetapas-container" class="mb-3"></div>
  `;

  container.appendChild(wrapper);

  const etapasDiv = wrapper.querySelector<HTMLDivElement>('#etapas');

  if (!etapasDiv) return;

  etapasDiv.innerHTML = '<p>Carregant etapes...</p>';

  try {
    const response = await getEtapas();
    const etapas: Etapa[] = response.data ?? [];

    etapasDiv.innerHTML = '';

    if (etapas.length === 0) {
      etapasDiv.innerHTML = '<div class="alert alert-info">No hi ha cap etapa històrica.</div>';
      return;
    }

    etapas.forEach((etapa) => {
      const btn = document.createElement('button');

      btn.type = 'button';
      btn.className = 'btn btn-outline-primary';
      btn.textContent = etapa.etapaNom;
      btn.dataset.etapaId = etapa.id;

      btn.addEventListener('click', async () => {
        state.etapa = etapa.id;
        state.subetapa = null;

        setActiveEtapaUI();

        await loadSubetapas();
        await loadEventos();
      });

      etapasDiv.appendChild(btn);
    });

    // Selecciona la primera etapa si encara no n'hi ha cap de seleccionada.
    if (!state.etapa || !etapas.some((etapa) => etapa.id === state.etapa)) {
      state.etapa = etapas[0].id;
    }

    setActiveEtapaUI();

    await loadSubetapas();
    await loadEventos();
  } catch (error: unknown) {
    console.error('Error en renderFiltros:', error);

    etapasDiv.innerHTML = `
    <div class="alert alert-danger">
      Error carregant les etapes històriques.
      Consulta la consola per veure el detall.
    </div>
  `;
  }
}

export function setActiveEtapaUI(): void {
  const buttons = document.querySelectorAll<HTMLButtonElement>('#etapas button');

  buttons.forEach((btn) => {
    btn.classList.toggle('active', btn.dataset.etapaId === state.etapa);
  });
}
