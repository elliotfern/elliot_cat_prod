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
  id: string;
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
  anyNaixement: number;
  mesNaixement: number;
  diaNaixement: number;
  anyDefuncio: number;
  mesDefuncio: number;
  diaDefuncio: number;
  grups: GrupDTO[];
}

type GrupDTO = { id: string; nom: string };

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
    id: '',
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
    const id = (data.id ?? '').toString();

    if (!id) {
      console.error('ID de persona no disponible');
      return;
    }

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formPersona', API_URLS.PUT.PERSONA(id));
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nova Persona</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formPersona', API_URLS.POST.PERSONA, true);
    });
  }

  const grupIds = data.grups?.map((g: { id: string; nom: string }) => g.id) ?? [];
  await auxiliarSelect(grupIds, 'grups', 'grups', 'grup_ca');

  await auxiliarSelect(data.imgId ?? 0, 'auxiliarImatgesAutor', 'imgId', 'alt');
  await auxiliarSelect(data.paisAutorId ?? 0, 'paisos', 'paisAutorId', 'pais_cat');
  await auxiliarSelect(data.genereId ?? 0, 'sexes', 'sexeId', 'nom');
  await auxiliarSelect(data.ciutatNaixementId ?? 0, 'ciutats', 'ciutatNaixementId', 'city');
  await auxiliarSelect(data.ciutatDefuncioId ?? 0, 'ciutats', 'ciutatDefuncioId', 'city');
  await auxiliarSelect(data.diaNaixement ?? 0, 'calendariDies', 'diaNaixement', 'dia');
  await auxiliarSelect(data.diaDefuncio ?? 0, 'calendariDies', 'diaDefuncio', 'dia');
  await auxiliarSelect(data.mesNaixement ?? 0, 'calendariMesos', 'mesNaixement', 'mes');
  await auxiliarSelect(data.mesDefuncio ?? 0, 'calendariMesos', 'mesDefuncio', 'mes');
}
