import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';
import { setTrixHTML } from '../../utils/setTrix';

interface Fitxa {
  [key: string]: unknown;
  status: string;
  message: string;
  id: number;
  espai_cat: string;
  municipi: number;
  comarca: number;
  provincia: number;
  comunitat: number;
  estat: number;
  experiencia_id: number;
  institucio_localitzacio: number;
  logo_id: number;
  sexe_id: number;
  pais_autor_id: number;
  img_id: number;
  ciutat_naixement_id: number;
  ciutat_defuncio_id: number;
  descripcio: string;
  grup_ids: number;
  any_naixement: number;
  mes_naixement: number;
  dia_naixement: number;
  any_defuncio: number;
  mes_defuncio: number;
  dia_defuncio: number;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export async function formPersona(isUpdate: boolean, slug?: string) {
  const form = document.getElementById('formPersona');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnPersona') as HTMLButtonElement;

  let data: Partial<Fitxa> = {
    comarca: 0,
    provincia: 0,
    comunitat: 0,
    estat: 0,
  };

  if (!divTitol || !btnSubmit || !form) return;

  if (slug && isUpdate) {
    const response = await fetchDataGet<ApiResponse<Fitxa>>(API_URLS.GET.PERSONA_DETALL_SLUG(slug), true);

    if (!response || !response.data) return;
    data = response.data;

    divTitol.innerHTML = `<h2>Modificació dades Persona</h2>`;

    renderFormInputs(data);

    // Carga robusta en Trix (después de que Trix se haya inicializado)
    await setTrixHTML('fites', data.descripcio);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formPersona', API_URLS.PUT.PERSONA);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nova Persona</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formPersona', API_URLS.POST.PERSONA, true);
    });
  }
  const grupsSeleccionats = data.grup_ids || [];

  await auxiliarSelect(data.img_id ?? 0, 'auxiliarImatgesAutor', 'imgId', 'alt');
  await auxiliarSelect(data.pais_autor_id ?? 0, 'pais', 'paisAutorId', 'pais_cat');
  await auxiliarSelect(data.grup_ids ?? 0, 'grup', 'grups', 'grup_ca');
  await auxiliarSelect(data.sexe_id ?? 0, 'sexe', 'sexeId', 'genereCa');
  await auxiliarSelect(data.ciutat_naixement_id ?? 0, 'ciutat', 'ciutatNaixementId', 'city');
  await auxiliarSelect(data.ciutat_defuncio_id ?? 0, 'ciutat', 'ciutatDefuncioId', 'city');
  await auxiliarSelect(data.dia_naixement ?? 0, 'calendariDies', 'diaNaixement', 'dia');
  await auxiliarSelect(data.dia_defuncio ?? 0, 'calendariDies', 'diaDefuncio', 'dia');
  await auxiliarSelect(data.mes_naixement ?? 0, 'calendariMesos', 'mesNaixement', 'mes');
  await auxiliarSelect(data.mes_defuncio ?? 0, 'calendariMesos', 'mesDefuncio', 'mes');
}
