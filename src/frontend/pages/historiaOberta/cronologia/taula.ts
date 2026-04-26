import { state } from "../../../types/Esdeveniment";

export function renderTabla(container: HTMLElement) {
  const table = document.createElement("table");
  table.className = "table table-striped";

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
  const tbody = document.querySelector("tbody")!;
  const start = (state.paginaActual - 1) * state.eventosPorPagina;
  const data = state.eventos.slice(start, start + state.eventosPorPagina);

  tbody.innerHTML = data.map(e => `
    <tr>
      <td>${e.esdeNom}</td>
      <td>${e.ciutat}</td>
      <td>${e.esdeDataIAny}</td>
      <td>${e.etapaNom}</td>
      <td>${e.nomSubEtapa}</td>
      <td>
        <a href="/gestio/historia/modifica-esdeveniment/${e.slug}" 
           class="btn btn-sm btn-primary">
           Modifica
        </a>
      </td>
    </tr>
  `).join("");
}