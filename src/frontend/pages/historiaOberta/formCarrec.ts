import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../Presentation/Utils/renderInputsForm';
import { Carrec } from '../../types/Carrec';

export async function formCarrec(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formCarrec');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnCarrec') as HTMLButtonElement;

  let data: Partial<Carrec> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    try {
      data = await api.get<Carrec>(API_URLS.GET.CARREC_ID(id));
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació de càrrec/responsabilitat</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formCarrec', API_URLS.PUT.CARREC_HISTORIA);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou càrrec/responsabilitat</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formCarrec', API_URLS.POST.CARREC_HISTORIA, true);
    });
  }

  await auxiliarSelect(data.persona_id ?? 0, 'persones', 'persona_id', 'nom_complet');
  await auxiliarSelect(data.organitzacio_id ?? 0, 'organitzacions', 'organitzacio_id', 'organitzacio');
}
