import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../Presentation/Utils/renderInputsForm';
import { CursHistoria } from '../../types/CursHistoria';
import { Organitzacio } from '../../types/Organitzacio';

export async function formOrganitzacioHistoria(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formOrganitzacio');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnOrg') as HTMLButtonElement;

  let data: Partial<Organitzacio> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    try {
      data = await api.get<CursHistoria>(API_URLS.GET.ORGANITZACIO_ID(id));
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació organització</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formOrganitzacio', API_URLS.PUT.ORGANITZACIO_HISTORIA);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nova organització</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formOrganitzacio', API_URLS.POST.ORGANITZACIO_HISTORIA, true);
    });
  }

  await auxiliarSelect(data.ideologia_id ?? 0, 'ideologies', 'ideologia_id', 'ideologia');
  await auxiliarSelect(data.pais_id ?? 0, 'paisos', 'pais_id', 'pais');
  await auxiliarSelect(data.ciutat_id ?? 0, 'ciutats', 'ciutat_id', 'ciutat');
  await auxiliarSelect(data.sub_etapa_id ?? 0, 'subEtapesHistoriques', 'sub_etapa_id', 'nomSubEtapa');
  await auxiliarSelect(data.imatge_id ?? 0, 'imgOrg', 'imatge_id', 'alt');
}
