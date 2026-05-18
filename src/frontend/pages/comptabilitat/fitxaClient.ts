import { formatData } from '../../utils/formataData';

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

  ciutat_ca: string | null;
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

  const value = (v: any) => (v === null || v === '' ? '—' : v);

  container.innerHTML = `
    <div class="client-card">
      <h2>${value(client.clientNom)} ${value(client.clientCognoms)}</h2>

      <p><strong>Email:</strong> ${value(client.clientEmail)}</p>
      <p><strong>Web:</strong> ${value(client.clientWeb)}</p>
      <p><strong>NIF:</strong> ${value(client.clientNIF)}</p>
      <p><strong>Empresa:</strong> ${value(client.clientEmpresa)}</p>

      <hr />

      <p><strong>Direcció:</strong> ${value(client.clientAdreca)} (${value(client.clientCP)})</p>

      <p><strong>Ciutat:</strong> ${value(client.ciutat_ca)}</p>
      <p><strong>Província:</strong> ${value(client.provincia_ca)}</p>
      <p><strong>País:</strong> ${value(client.pais_ca)}</p>

      <hr />

      <p><strong>Telèfon:</strong> ${value(client.clientTelefon)}</p>
      <p><strong>Data registre:</strong> ${value(formatData(client.clientRegistre))}</p>

      <p><strong>Estat:</strong> ${value(client.estat)}</p>
    </div>
  `;
}
