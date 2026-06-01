import { api } from '../../core/api/client';
import { Despesa } from '../../types/Despesa';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formDespesa(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formDespesa') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnDespesa') as HTMLButtonElement | null;
  if (!form || !divTitol || !btnSubmit) return;

  let data: Partial<Despesa> = {};

  if (isUpdate && id) {
    try {
      data = await api.get<Despesa>(API_URLS.GET.DESPESA_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació Factura Despesa</h2>`;
    btnSubmit.textContent = 'Modificar Despesa';

    renderFormInputs(data);

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'PUT', 'formDespesa', API_URLS.PUT.DESPESA);
    });
  } else {
    divTitol.innerHTML = `<h2>Nova Factura de Despesa</h2>`;
    btnSubmit.textContent = 'Crear Despesa';

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'POST', 'formDespesa', API_URLS.POST.DESPESA, true);
    });
  }

  // --- Selects auxiliares ---
  await auxiliarSelect(data.proveidor_id ?? null, 'proveidors', 'proveidor_id', 'nom');
  await auxiliarSelect(data.receptor_id ?? null, 'emissors', 'receptor_id', 'nom');
  await auxiliarSelect(data.categoria_id ?? null, 'categories_despeses', 'categoria_id', 'nom');
  await auxiliarSelect(data.subcategoria_id ?? null, 'sub_categories_despeses', 'subcategoria_id', 'nom');
  await auxiliarSelect(data.metode_pagament ?? null, 'metodes_pagament_despeses', 'metode_pagament', 'label');
  await auxiliarSelect(data.tipus_despesa ?? null, 'tipus_despeses', 'tipus_despesa', 'label');
  await auxiliarSelect(data.frequencia ?? null, 'frequencies', 'frequencia', 'label');
  await auxiliarSelect(data.projecte_id ?? null, 'projectes', 'projecte_id', 'name');
  await auxiliarSelect(data.client_id ?? null, 'clients', 'client_id', 'clientEmpresa');
}
