import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { Vault, TwoFACodeResponse } from '../../types/Vault';
import { TaulaDinamica } from '../../types/TaulaDinamica';

export async function serveisVaultApi() {
  const isAdmin = await getIsAdmin(); // Comprobar si es admin
  let gestioUrl: string = '';

  if (isAdmin) {
    gestioUrl = '/gestio';
  }

  const columns: TaulaDinamica<Vault>[] = [
    {
      header: 'Servei',
      field: 'servei',
      render: (_: unknown, row: Vault) => `<a id="${row.id}" href="${row.web}" target="_blank">${row.servei}</a>`,
    },
    { header: 'Usuari', field: 'usuari' },
    {
      header: 'Contrasenya',
      field: 'id',
      render: (_: unknown, row: Vault) => `
        <div class="input-group">
          <input class="form-control input-petit" type="password" name="role" id="passw-${row.id}" value="*******" readonly>
         <button type="button" class="btn-petit btn-primari show-pass-btn" data-id="${row.id}">Mostrar</button>
        </div>
      `,
    },
    {
      header: 'Clau 2F',
      field: 'id',
      render: (_: unknown, row: Vault) => `
        <div class="input-group">
          <input class="form-control input-petit" type="password" name="role" id="clau2f-${row.id}" value="*******" readonly>
         <button type="button" class="btn-petit btn-primari show-clau2f-btn" data-id="${row.id}">Mostrar</button>
        </div>
      `,
    },
    { header: 'Tipus', field: 'tipus' },
    {
      header: 'Data modificació',
      field: 'dateModified',
      render: (_: unknown, row: Vault) => {
        const inici = formatData(row.dateModified);
        return `${inici}`;
      },
    },
  ];

  if (isAdmin) {
    columns.push({
      header: '',
      field: 'id',
      render: (_: unknown, row: Vault) => `
        <a href="https://${window.location.host}${gestioUrl}/claus-privades/modifica-vault/${row.id}">
           <button type="button" class="button btn-petit">Modifica</button></a>`,
    });

    columns.push({
      header: '',
      field: 'id',
      render: (_: unknown, row: Vault) => `
        <a href="https://${window.location.host}${gestioUrl}/claus-privades/modifica-vault/${row.id}">
           <button type="button" class="btn-petit btn-secondari">Elimina</button></a>`,
    });
  }

  renderDynamicTable({
    url: `https://${window.location.host}/api/vault/get/?llistat_serveis`,
    containerId: 'taulaLlistatVault',
    columns,
    filterKeys: ['servei'],
    filterByField: 'tipus',
  });

  document.getElementById('taulaLlistatVault')?.addEventListener('click', (event) => {
    const target = event.target as HTMLElement;

    // Busca si se ha hecho clic en un botón con clase `.show-pass-btn`
    if (target.classList.contains('show-pass-btn')) {
      const id = parseInt(target.getAttribute('data-id') || '', 10);
      if (!isNaN(id)) {
        showPass(id);
      }
    }

    // Busca si se ha hecho clic en un botón con clase `.show-clau2f-btn`
    if (target.classList.contains('show-clau2f-btn')) {
      const id = parseInt(target.getAttribute('data-id') || '', 10);
      if (!isNaN(id)) {
        show2FACode(id);
      }
    }
  });
}

// Función para mostrar/ocultar la contraseña
function showPass(id: number): void {
  const inputField = document.getElementById(`passw-${id}`) as HTMLInputElement;
  const urlAjax = `/api/vault/get/?id=${id}`;

  if (inputField.type === 'password') {
    fetch(urlAjax, { method: 'GET', headers: { Accept: 'application/json' } })
      .then((response) => (response.ok ? response.json() : Promise.reject('Error en la solicitud AJAX')))
      .then((data: { password?: string; error?: string }) => {
        if (data.password) {
          inputField.value = data.password;
          inputField.type = 'text';
          navigator.clipboard.writeText(data.password).catch(console.error);

          setTimeout(() => {
            inputField.value = '**********';
            inputField.type = 'password';
          }, 5000);
        } else {
          inputField.value = data.error || 'Error desconocido';
          inputField.type = 'text';
        }
      })
      .catch((error) => {
        console.error(error);
        alert('Hubo un problema al intentar obtener la contraseña.');
      });
  }
}

// Función para mostrar/ocultar el código 2FA
function show2FACode(id: number): void {
  const inputField = document.getElementById(`clau2f-${id}`) as HTMLInputElement;
  const urlAjax = `/api/vault/get/?type=codigo2f&id2F=${id}`;

  // Comprobamos si el campo de entrada existe y es de tipo "password"
  if (!inputField) {
    console.error(`Input field with id "clau2f-${id}" not found`);
    return;
  }

  if (inputField.type === 'password') {
    fetch(urlAjax, { method: 'GET', headers: { Accept: 'application/json' } })
      .then((response) => response.json())
      .then((data: TwoFACodeResponse) => {
        if (data.code) {
          // Mostrar el código 2FA en el campo correspondiente
          inputField.value = data.code;
          inputField.type = 'text'; // Mostrar el código

          // Copiar al portapapeles si es necesario
          navigator.clipboard.writeText(data.code).catch((error) => {
            console.error('Error al copiar el código 2FA al portapapeles:', error);
          });

          // Ocultar el código después de 5 segundos
          setTimeout(() => {
            inputField.value = '*******'; // Volver al placeholder
            inputField.type = 'password';
          }, 5000);
        } else {
          alert('Error al obtener el código 2FA');
        }
      })
      .catch((error) => {
        console.error('Error al obtener el código 2FA:', error);
        alert('Hubo un problema al intentar obtener el código 2FA.');
      });
  }
}
