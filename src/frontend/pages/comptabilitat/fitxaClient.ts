import { formatDataCatala } from '../../utils/formataData';
import { renderClientFactures } from './fitxaClientFactures';
import { renderClientPressupostos } from './fitxaClientPressupostos';

type ClientDTO = {
  id: string;
  clientNom: string | null;
  clientCognoms: string | null;
  clientEmail: string | null;
  clientWeb: string | null;
  clientNIF: string | null;
  clientEmpresa: string | null;
  clientAdreca: string | null;
  clientCP: string | null;
  clientTelefon: number | null;
  clientRegistre: string;

  ciutat_final: string | null;
  provincia_ca: string | null;
  pais_ca: string | null;

  estat: string | null;
};

type ApiResponse = {
  status: string;
  message: string;
  data: ClientDTO | null;
};

export async function fitxaClient(id: string) {
  const res = await fetch(`https://elliot.cat/api/comptabilitat/get/clientId?id=${id}`);

  const json: ApiResponse = await res.json();

  renderClient(json);
}

function renderClient(response: ApiResponse) {
  const container = document.getElementById('fitxaClient');
  if (!container) return;

  const client = response.data;

  if (!client) {
    container.innerHTML = `<p>No s'ha trobat el client</p>`;
    return;
  }

  renderClientPressupostos(client.id);
  renderClientFactures(client.id);

  const v = (x: any) => (x === null || x === '' ? '—' : x);

  container.innerHTML = `
    <div class="card shadow-sm">

      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
          ${v(client.clientNom)} ${v(client.clientCognoms)}
        </h4>

        <span class="badge bg-primary">
          ${v(client.estat)}
        </span>

        <a
          href="https://elliot.cat/gestio/comptabilitat/modifica-client/${client.id}"
          class="btn btn-secondary btn-sm">
          Modifica client
        </a>

      </div>

      <div class="card-body">

        <div class="row g-4">

          <!-- CONTACTE -->
          <div class="col-md-6">
            <h6 class="text-muted mb-2">Contacte</h6>
            <p class="mb-1"><strong>Email:</strong> ${v(client.clientEmail)}</p>
            <p class="mb-1"><strong>Telèfon:</strong> ${v(client.clientTelefon)}</p>
            <p class="mb-1"><strong>Web:</strong> ${v(client.clientWeb)}</p>
          </div>

          <!-- FISCAL -->
          <div class="col-md-6">
            <h6 class="text-muted mb-2">Dades fiscals</h6>
            <p class="mb-1"><strong>NIF:</strong> ${v(client.clientNIF)}</p>
            <p class="mb-1"><strong>Empresa:</strong> ${v(client.clientEmpresa)}</p>
          </div>

          <!-- ADREÇA -->
          <div class="col-12">
            <h6 class="text-muted mb-2">Adreça</h6>
            <p class="mb-1">${v(client.clientAdreca)}</p>
            <p class="mb-1">
              ${v(client.clientCP)} · ${v(client.ciutat_final)}
            </p>
            <p class="mb-1">
              ${v(client.provincia_ca)} · ${v(client.pais_ca)}
            </p>
          </div>

        </div>

      </div>

      <div class="card-footer text-muted small">
        Data d'alta: ${formatDataCatala(v(client.clientRegistre))}
      </div>

    </div>
  `;
}
