import { api } from '../../core/api/client';
import { Pressupost } from '../../types/Pressupost';

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('ca-ES');
}

function formatMoney(value: number): string {
  return new Intl.NumberFormat('ca-ES', {
    style: 'currency',
    currency: 'EUR',
  }).format(value);
}

export async function fitxaPressupost(id: string) {
  let data: Pressupost;
  try {
    data = await api.get<Pressupost>(`comptabilitat/get/pressupostId`, {
      id,
    });
    renderPressupost(data);
  } catch (error) {
    console.error(error);

    return;
  }
}

function renderPressupost(pressupost: Pressupost) {
  const container = document.getElementById('fitxaPressupost');
  if (!container) return;

  if (!pressupost) {
    container.innerHTML = `<p>No s'ha trobat el pressupost</p>`;
    return;
  }

  container.innerHTML = `
    <div class="card shadow-sm border-0">

      <!-- HEADER -->
      <div class="card-header bg-dark text-white d-flex justify-content-between">
        <div>
          <strong>Pressupost</strong> #${pressupost.id.slice(0, 8)}
        </div>

        <div class="text-end">
          <span class="badge bg-light text-dark">
            ${pressupost.estat ?? 'Sense estat'}
          </span>

           <a
          href="https://elliot.cat/gestio/comptabilitat/modifica-pressupost/${pressupost.id}"
          class="btn btn-secondary btn-sm">
          Modifica pressupost
        </a>

        </div>
      </div>

      <div class="card-body">

        <!-- TITOL -->
        <h4 class="mb-3">${pressupost.concepte}</h4>

        <div class="row g-3">

          <!-- IMPORT -->
          <div class="col-md-4">
            <div class="p-3 bg-light rounded">
              <div class="text-muted small">Import</div>
              <div class="fs-5 fw-bold text-success">
                ${formatMoney(pressupost.import)}
              </div>
            </div>
          </div>

          <!-- DATA -->
          <div class="col-md-4">
            <div class="p-3 bg-light rounded">
              <div class="text-muted small">Data</div>
              <div class="fw-semibold">
                ${formatDate(pressupost.data)}
              </div>
            </div>
          </div>

          <!-- ANY -->
          <div class="col-md-4">
            <div class="p-3 bg-light rounded">
              <div class="text-muted small">Any</div>
              <div class="fw-semibold">
                ${pressupost.any ?? '-'}
              </div>
            </div>
          </div>

          <!-- CLIENT -->
          <div class="col-md-6">
            <div class="p-3 bg-light rounded">
              <div class="text-muted small">Client</div>
              <div class="fw-semibold">
                ${pressupost.clientNom} ${pressupost.clientCognoms ?? ''}
              </div>
              <div class="small text-muted">
                ${pressupost.clientEmail ?? ''}
              </div>
              <div class="small text-muted">
                ${pressupost.clientEmpresa ?? ''}
              </div>
            </div>
          </div>

          <!-- PRODUCTE / SERVEI -->
          <div class="col-md-6">
            <div class="p-3 bg-light rounded">
              <div class="text-muted small">Servei</div>
              <div class="fw-semibold">
                ${pressupost.producte ?? '-'}
              </div>
            </div>
          </div>

          <!-- IDS DEBUG (opcional però útil en admin) -->
          <div class="col-12">
            <div class="small text-muted mt-2">
              client_id: ${pressupost.client_id} <br>
              servei_id: ${pressupost.servei_id} <br>
              estat_id: ${pressupost.estat_id}
            </div>
          </div>

        </div>
      </div>

      <div class="card-footer text-muted small">
        Creat: ${formatDate(pressupost.created_at)} · Modificat: ${formatDate(pressupost.modified_at)}
      </div>

    </div>
  `;

  container.style.display = 'block';
}
