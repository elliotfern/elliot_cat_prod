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
  genereId: number;
  paisAutorId: number;
  imgId: number;
  ciutatNaixementId: number;
  ciutatDefuncioId: number;
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
    await setTrixHTML('descripcio', data.descripcio);

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

  await auxiliarSelect(data.imgId ?? 0, 'auxiliarImatgesAutor', 'imgId', 'alt');
  await auxiliarSelect(data.paisAutorId ?? 0, 'paisos', 'paisAutorId', 'pais_cat');
  await auxiliarSelect(data.grup_ids ?? 0, 'grups', 'grups', 'grup_ca');
  await auxiliarSelect(data.genereId ?? 0, 'sexes', 'sexeId', 'nom');
  await auxiliarSelect(data.ciutatNaixementId ?? 0, 'ciutats', 'ciutatNaixementId', 'city');
  await auxiliarSelect(data.ciutatDefuncioId ?? 0, 'ciutats', 'ciutatDefuncioId', 'city');
  await auxiliarSelect(data.dia_naixement ?? 0, 'calendariDies', 'diaNaixement', 'dia');
  await auxiliarSelect(data.dia_defuncio ?? 0, 'calendariDies', 'diaDefuncio', 'dia');
  await auxiliarSelect(data.mes_naixement ?? 0, 'calendariMesos', 'mesNaixement', 'mes');
  await auxiliarSelect(data.mes_defuncio ?? 0, 'calendariMesos', 'mesDefuncio', 'mes');
}
