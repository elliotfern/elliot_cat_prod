import { api } from '../../core/api/client';
import { Persona } from '../../types/Persona';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';
import { setTrixHTML } from '../../utils/setTrix';

export async function formPersona(isUpdate: boolean, slug?: string) {
  const form = document.getElementById('formPersona');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnPersona') as HTMLButtonElement;

  let data: Partial<Persona> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (slug && isUpdate) {
    try {
      data = await api.get<Persona>(`persones/get/persona`, {
        slug,
      });
    } catch (error) {
      console.error(error);

      return;
    }

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
      transmissioDadesDB(event, 'POST', 'formPersona', `https://elliot.cat/api/persones/put/?persona=${id}`);
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
  await auxiliarSelect(data.ciutat_naixement_id ?? 0, 'ciutats', 'ciutat_naixement_id', 'ciutat');
  await auxiliarSelect(data.ciutat_defuncio_id ?? 0, 'ciutats', 'ciutat_defuncio_id', 'ciutat');
  await auxiliarSelect(data.dia_naixement ?? 0, 'calendariDies', 'dia_naixement', 'dia');
  await auxiliarSelect(data.dia_defuncio ?? 0, 'calendariDies', 'dia_defuncio', 'dia');
  await auxiliarSelect(data.mes_naixement ?? 0, 'calendariMesos', 'mes_naixement', 'mes');
  await auxiliarSelect(data.mes_defuncio ?? 0, 'calendariMesos', 'mes_defuncio', 'mes');
}
