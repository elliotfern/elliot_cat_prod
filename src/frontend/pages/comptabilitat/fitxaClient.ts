import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { Client } from '../../types/Client';
import { Button } from '../../Presentation/Components/Button/Button';
import { API_URLS } from '../../utils/apiUrls';
import { formatDataCatala } from '../../utils/formataData';
import { INTRANET_URLS } from '../../utils/IntranetUrls';
import { renderClientFactures } from './fitxaClientFactures';
import { renderClientPressupostos } from './fitxaClientPressupostos';

export async function fitxaClient(id: string) {
  let data: Client;

  try {
    data = await api.get<Client>(API_URLS.GET.CLIENT_ID, {
      id,
    });
  } catch (error) {
    console.error(error);

    return;
  }

  renderClient(data);
}

function renderClient(response: Client) {
  const container = document.getElementById('fitxaClient');
  if (!container) return;

  const client = response;

  if (!client) {
    container.innerHTML = `<p>No s'ha trobat el client</p>`;
    return;
  }

  renderClientPressupostos(client.id);
  renderClientFactures(client.id);

  const v = (x: unknown) => (x === null || x === '' ? '—' : x);

  container.innerHTML = `
    <div class="card shadow-sm">

      <div class="card-header d-flex justify-content-between align-items-center">

        <h4 class="mb-0">
          ${v(client.nom)} ${v(client.cognoms)}
        </h4>

        <div class="d-flex align-items-center gap-2">

        <span id="btnModificarContacte"></span>
         <span id="btnModificarClient"></span>

        </div>

      </div>

      <div class="card-body">
        <div class="col-md-4" style="margin-bottom:25px">
          <span class="badge bg-primary">
            ${v(client.estat)}
          </span>
        </div>

        <div class="row g-4">

              <!-- CONTACTE -->
          <div class="col-md-6">
            <h6 class="text-muted mb-2">Contacte</h6>
            <p class="mb-1"><strong>Email:</strong> ${v(client.email)}</p>
            <p class="mb-1"><strong>Telèfon:</strong> ${v(client.tel_1)} - ${v(client.tel_2)}</p>
            <p class="mb-1"><strong>Web:</strong> ${v(client.web)}</p>
          </div>

          <!-- FISCAL -->
          <div class="col-md-6">
            <h6 class="text-muted mb-2">Dades fiscals</h6>
            <p class="mb-1"><strong>NIF:</strong> ${v(client.nif)}</p>
            <p class="mb-1"><strong>Empresa:</strong> ${v(client.empresa)}</p>
          </div>

          <!-- ADREÇA -->
          <div class="col-12">
            <h6 class="text-muted mb-2">Adreça</h6>

            <p class="mb-1">
              ${v(client.adreca)}
            </p>

            <p class="mb-1">
              ${v(client.cp)} · ${v(client.ciutat)}
            </p>

            <p class="mb-1">
              ${v(client.provincia)} · ${v(client.pais)}
            </p>
          </div>

        </div>

      </div>

      <div class="card-footer text-muted small">
        <div>
          Data d'alta: ${client.created_at ? formatDataCatala(client.created_at) : '—'}
        </div>
        <div>
          Darrera actualització: ${client.updated_at ? formatDataCatala(client.updated_at) : '—'}
        </div>
      </div>

    </div>
  `;

  // Botó modificar
  const btnContainer = document.getElementById('btnModificarContacte');

  if (btnContainer) {
    btnContainer.appendChild(Button.edit('Modifica contacte', INTRANET_URLS.CONTACTES.CONTACTE_MODIFICA_ID(client.id)));
  }

  const btnContainer2 = document.getElementById('btnModificarClient');

  if (btnContainer2) {
    btnContainer2.appendChild(Button.edit2('Modifica client', INTRANET_URLS.COMPTABILITAT.CLIENT_MODIFICA_ID(client.id)));
  }
}
