import { api } from '../../core/api/client';
import { Tasca } from '../../types/Tasca';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formTask(isUpdate: boolean, id?: number) {
  let data: Partial<Tasca> = {};

  async function waitForElement(idEl: string, timeoutMs = 4000): Promise<HTMLElement | null> {
    const start = Date.now();
    while (Date.now() - start < timeoutMs) {
      const el = document.getElementById(idEl);
      if (el) return el;
      await new Promise((r) => setTimeout(r, 25));
    }
    return null;
  }

  // ⬅️ CLAVE: esperar el form (porque se inyecta más tarde)
  await waitForElement('taskForm');

  const form = document.getElementById('taskForm') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLSpanElement | HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnProjecte') as HTMLButtonElement | null;

  if (!divTitol || !btnSubmit || !form) return;

  // ✅ guard
  if (form.dataset.inited === '1') return;
  form.dataset.inited = '1';

  // ahora ya sí:
  await waitForElement('project_id');
  await waitForElement('status');
  await waitForElement('priority');

  // UI helper: mostrar/ocultar blocked_reason según status
  function syncBlockedUI(statusVal: number) {
    const wrap = document.getElementById('blockedWrap');
    const input = document.getElementById('blocked_reason') as HTMLInputElement | null;

    if (!wrap || !input) return;

    if (Number(statusVal) === 3) {
      wrap.classList.remove('d-none');
      input.required = true; // si lo quieres obligatorio
    } else {
      wrap.classList.add('d-none');
      input.required = false;
      input.value = '';
    }
  }

  // Hook change status siempre (create y update)
  const statusSel = document.getElementById('status') as HTMLSelectElement | null;
  if (statusSel) {
    statusSel.addEventListener('change', () => {
      syncBlockedUI(Number(statusSel.value));
    });
  }

  if (id && isUpdate) {
    try {
      data = await api.get<Tasca>(API_URLS.GET.TASK_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    // Título y botón
    if ('innerHTML' in divTitol) {
      (divTitol as HTMLDivElement).innerHTML = `Modificació de la tasca`;
    } else {
      (divTitol as HTMLSpanElement).textContent = `Modificació de la tasca`;
    }
    btnSubmit.textContent = 'Modificar dades';

    // 1) cargar selects con preselección
    // OJO: cambia 'projectes' por el "type" real que tengas en auxiliarSelect
    await auxiliarSelect(data.project_id ?? null, 'projectes', 'project_id', 'name');

    // 2) rellenar inputs (ahora ya existen opciones)
    renderFormInputs(data);

    // 3) estado UI blocked
    syncBlockedUI(Number(data.status ?? 1));

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'taskForm', API_URLS.PUT.TASCA);
    });
  } else {
    if ('innerHTML' in divTitol) {
      (divTitol as HTMLDivElement).innerHTML = `Creació de nova tasca`;
    } else {
      (divTitol as HTMLSpanElement).textContent = `Creació de nova tasca`;
    }
    btnSubmit.textContent = 'Inserir dades';

    // cargar selects sin selección
    await auxiliarSelect(null, 'projectes', 'project_id', 'name');

    // asegurar estado UI inicial
    syncBlockedUI(Number(statusSel?.value ?? 1));

    form.addEventListener('submit', function (event) {
      if (btnSubmit.disabled) return;
      btnSubmit.disabled = true;
      transmissioDadesDB(event, 'POST', 'taskForm', API_URLS.POST.TASCA, true);
      setTimeout(() => (btnSubmit.disabled = false), 2000); // o re-habilitar en callback si tu helper lo soporta
    });
  }
}
