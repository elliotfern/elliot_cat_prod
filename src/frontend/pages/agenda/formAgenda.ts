import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

type TipusEsdeveniment = 'reunio' | 'visita_medica' | 'videotrucada' | 'altre';
type EstatEsdeveniment = 'pendent' | 'confirmat' | 'cancel·lat';

interface EsdevenimentAgenda {
  status: string;
  message: string;

  id: number;

  titol: string;
  descripcio: string | null;
  tipus: TipusEsdeveniment;

  lloc: string | null;

  data_inici: string; // backend format
  data_fi: string; // backend format

  tot_el_dia: number; // 0/1
  estat: EstatEsdeveniment;

  creat_el?: string;
  actualitzat_el?: string;
  usuari_id?: number;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

/**
 * IMPORTANT:
 * - En tu HTML, los selects deben existir con id="tipus" y id="estat"
 * - Pueden estar vacíos; auxiliarSelect los rellena.
 */
export async function formAgendaEsdeveniment(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formCrearEsdeveniment') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnGuardar') as HTMLButtonElement | null;

  if (!divTitol || !btnSubmit || !form) return;

  // Defaults de CREATE
  let data: Partial<EsdevenimentAgenda> = {
    tot_el_dia: 0,
    estat: 'confirmat',
    tipus: 'altre',
    descripcio: null,
    lloc: null,
  };

  // 1) Si es UPDATE: cargar evento
  if (id && isUpdate) {
    const idNum = Number(id);
    if (!Number.isFinite(idNum) || idNum <= 0) return;

    const response = await fetchDataGet<ApiResponse<EsdevenimentAgenda>>(API_URLS.GET.AGENDA_ID(idNum), true);

    if (!response || !response.data) return;
    data = response.data;

    divTitol.innerHTML = `<h2>Modificar esdeveniment</h2>`;
    btnSubmit.textContent = 'Modificar dades';

    // Rellena inputs simples (text, textarea, datetime-local, checkbox...)
    renderFormInputs(data);

    // Rellena selects dinámicos (y deja seleccionado lo que toque)
    await carregarSelectsAgenda(data);

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formCrearEsdeveniment', API_URLS.PUT.AGENDA_ESDEVENIMENT);
    });

    return;
  }

  // 2) CREATE
  divTitol.innerHTML = `<h2>Creació de nou esdeveniment</h2>`;
  btnSubmit.textContent = 'Inserir dades';

  // Primero cargamos selects para que existan opciones
  await carregarSelectsAgenda(data);

  // Luego aplicamos defaults en el formulario (si quieres que se vean ya seleccionados)
  renderFormInputs(data);

  form.addEventListener('submit', function (event) {
    transmissioDadesDB(event, 'POST', 'formCrearEsdeveniment', API_URLS.POST.AGENDA_ESDEVENIMENT, true);
  });
}

/**
 * Carga selects dinámicos con auxiliarSelect.
 * Ajusta aquí los nombres del catálogo y los nombres de campos según tu API.
 */
async function carregarSelectsAgenda(data: Partial<EsdevenimentAgenda>): Promise<void> {
  // 👇 Ajusta estos 4 strings a tu realidad (catálogo + valueField + labelField).
  // Ejemplo esperado por tu auxiliarSelect:
  // auxiliarSelect(valorSeleccionado, 'catalogo', 'campo_value', 'campo_label');

  // TIPUS (enum en BD, pero tú quieres dinámico por catálogo/API)
  await auxiliarSelect((data.tipus ?? 'altre') as unknown as number | string, 'tipusAgenda', 'tipus', 'tipus_ca');

  // ESTAT
  await auxiliarSelect((data.estat ?? 'confirmat') as unknown as number | string, 'estatsAgenda', 'estat', 'estat_ca');
}
