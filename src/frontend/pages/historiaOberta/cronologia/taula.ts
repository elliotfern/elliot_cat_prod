import { state } from '../../../types/Esdeveniment';

const mesos = ['', 'gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];

export function formatDataCatalana(dia?: number, mes?: number, any?: number) {
  if (!any) return '';

  const d = dia && dia !== 0 ? dia : '';
  const m = mes && mes !== 0 ? mesos[mes] : '';

  if (d && m) return `${d} ${m} ${any}`;
  if (m) return `${m} ${any}`;

  return `${any}`;
}

export function renderTabla(container: HTMLElement) {
  const table = document.createElement('table');
  table.className = 'table table-striped';

  table.innerHTML = `
    <thead class="table-primary">
      <tr>
        <th>Esdeveniment</th>
        <th>Ciutat</th>
        <th>Data</th>
        <th>Etapa</th>
        <th>Època</th>
        <th></th>
      </tr>
    </thead>
    <tbody></tbody>
  `;

  container.appendChild(table);
}

export function pintarTabla() {
  const tbody = document.querySelector('tbody')!;
  const start = (state.paginaActual - 1) * state.eventosPorPagina;
  const data = state.eventos.slice(start, start + state.eventosPorPagina);

  tbody.innerHTML = data
    .map(
      (e) => `
    <tr>
    <td>
        <a href="/gestio/historia/fitxa-esdeveniment/${e.slug}">
           <strong>${e.esdeNom}</strong>
        </a>
      </td>
      <td>${e.ciutat}</td>
      <td>
        ${formatDataCatalana(e.esdeDataIDia, e.esdeDataIMes, e.esdeDataIAny)}
      </td>
      <td>${e.etapaNom}</td>
      <td>${e.nomSubEtapa}</td>
      <td>
        <a href="/gestio/historia/modifica-esdeveniment/${e.id}" 
           class="btn btn-sm btn-primary">
           Modifica
        </a>
      </td>
    </tr>
  `
    )
    .join('');
}
