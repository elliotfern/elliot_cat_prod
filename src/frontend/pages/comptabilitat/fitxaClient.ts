type ClientApiResponse = {
  status: string;
  data?: {
    id: string;
    clientNom: string | null;
    clientCognoms: string | null;
    clientEmail: string | null;
    clientWeb: string | null;
    clientNIF: string | null;
    clientEmpresa: string | null;
    clientAdreca: string | null;
    clientCP: string | null;
    clientTelefon: string | null;
    clientRegistre: string | null;
    ciutat_ca: string | null;
    pais_ca: string | null;
    provincia_ca: string | null;
    estat_id: string | null;
    estat: string | null;
  };
  message?: string;
};

function escape(value: unknown): string {
  if (value === null || value === undefined || value === '') return '—';
  return String(value);
}

export async function fitxaClient(clientId: string): Promise<void> {
  const container = document.getElementById('fitxaClient');

  if (!container) return;

  container.innerHTML = '<p>Carregant client...</p>';

  try {
    const res = await fetch(`https://elliot.cat/api/comptabilitat/get/clientId?id=${clientId}`);

    const json: ClientApiResponse = await res.json();

    if (json.status !== 'success' || !json.data) {
      container.innerHTML = `<p>Error carregant client</p>`;
      return;
    }

    const c = json.data;

    container.innerHTML = `
      <div class="card shadow-sm">
        <div class="card-body">

          <h3 class="mb-3">
            ${escape(c.clientNom)} ${escape(c.clientCognoms)}
          </h3>

          <div class="row g-2">

            <div class="col-md-6">
              <strong>Email:</strong> ${escape(c.clientEmail)}
            </div>

            <div class="col-md-6">
              <strong>Telèfon:</strong> ${escape(c.clientTelefon)}
            </div>

            <div class="col-md-6">
              <strong>Empresa:</strong> ${escape(c.clientEmpresa)}
            </div>

            <div class="col-md-6">
              <strong>NIF:</strong> ${escape(c.clientNIF)}
            </div>

            <div class="col-12">
              <strong>Adreça:</strong> ${escape(c.clientAdreca)} ${escape(c.clientCP)}
            </div>

            <div class="col-md-4">
              <strong>Ciutat:</strong> ${escape(c.ciutat_ca)}
            </div>

            <div class="col-md-4">
              <strong>Província:</strong> ${escape(c.provincia_ca)}
            </div>

            <div class="col-md-4">
              <strong>País:</strong> ${escape(c.pais_ca)}
            </div>

            <div class="col-md-6">
              <strong>Registre:</strong> ${escape(c.clientRegistre)}
            </div>

            <div class="col-md-6">
              <strong>Estat:</strong> ${escape(c.estat)}
            </div>

          </div>

        </div>
      </div>
    `;
  } catch (err) {
    container.innerHTML = `<p>Error de xarxa</p>`;
  }
}
