import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';
import { setTrixHTML } from '../../utils/setTrix';

interface Fitxa {
  [key: string]: unknown;
  grup_ids?: string[];
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

  // --- PERSONA / AUTOR (alineado con DB)
  sexe_id: number;
  pais_autor_id: number;
  img_id: number;

  ciutat_naixement_id: number;
  ciutat_defuncio_id: number;

  descripcio: string;

  any_naixement: number;
  mes_naixement: number;
  dia_naixement: number;

  any_defuncio: number;
  mes_defuncio: number;
  dia_defuncio: number;

  // --- relaciones
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
    //const response = await fetchDataGet<ApiResponse<Fitxa>>(API_URLS.GET.PERSONA_DETALL_SLUG(slug), true);
    const response = await fetchDataGet<ApiResponse<Fitxa>>(`https://elliot.cat/api/persones/get/?persona=${slug}`, true);

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
      //transmissioDadesDB(event, 'PUT', 'formPersona', API_URLS.PUT.PERSONA(id));
      transmissioDadesDB(event, 'PUT', 'formPersona', `https://elliot.cat/api/persones/put/?persona=${id}`);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nova Persona</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      //transmissioDadesDB(event, 'POST', 'formPersona', API_URLS.POST.PERSONA, true);
      transmissioDadesDB(event, 'POST', 'formPersona', 'https://elliot.cat/api/persones/post/?persona', true);
    });
  }

  const grupIds: string[] = Array.isArray(data.grup_ids) ? data.grup_ids : Array.isArray(data.grups) ? data.grups.map((g: { id: string }) => g.id) : [];
  await auxiliarSelect(grupIds, 'grups', 'grup_ids', 'grup_ca');

  await auxiliarSelect(data.img_id ?? 0, 'auxiliarImatgesAutor', 'img_id', 'alt');
  await auxiliarSelect(data.pais_autor_id ?? 0, 'paisos', 'pais_autor_id', 'pais_ca');
  await auxiliarSelect(data.sexe_id ?? 0, 'sexes', 'sexe_id', 'nom');
  await auxiliarSelect(data.ciutat_naixement_id ?? 0, 'ciutats', 'ciutat_naixement_id', 'city');
  await auxiliarSelect(data.ciutat_defuncio_id ?? 0, 'ciutats', 'ciutat_defuncio_id', 'city');
  await auxiliarSelect(data.dia_naixement ?? 0, 'calendariDies', 'dia_naixement', 'dia');
  await auxiliarSelect(data.dia_defuncio ?? 0, 'calendariDies', 'dia_defuncio', 'dia');
  await auxiliarSelect(data.mes_naixement ?? 0, 'calendariMesos', 'mes_naixement', 'mes');
  await auxiliarSelect(data.mes_defuncio ?? 0, 'calendariMesos', 'mes_defuncio', 'mes');
}
