type EspaiData = {
  nameImg: string;
  alt: string;
  tipusImatge: string;
  details: Record<string, string>;
};

// Función auxiliar para formatear la fecha (de "YYYY-MM-DD" a "DD-MM-YYYY")
function formatDate(dateStr: string): string {
  const [year, month, day] = dateStr.split('-');
  return `${day}-${month}-${year}`;
}

export function renderFitxaInformacio(data: EspaiData): HTMLElement {
  const wrapper = document.createElement('div');
  wrapper.className = 'fixaDades';

  // Clonamos el objeto details para no modificar el original
  const details = { ...data.details };

  // Extraer y eliminar los campos especiales si existen
  const dateCreated = details['Data de creació'];
  const dateModified = details['Última modificació'];
  const descr = details['Descripció'];
  const titol = details['Titol'];

  delete details['Data de creació'];
  delete details['Última modificació'];
  delete details['Descripció'];
  delete details['Titol'];

  // Construcción del bloque de detalles genéricos
  const detailHtml = Object.entries(details)
    .map(([label, value]) => {
      const safeValue = value ?? '';
      const isUrl = typeof safeValue === 'string' && safeValue.startsWith('http');

      return `<p><strong>${label}: </strong> ${isUrl ? `<a href="${safeValue}" target="_blank">web</a>` : safeValue}</p>`;
    })
    .join('');

  // HTML principal de la fitxa
  const hasImage = typeof data.nameImg === 'string' && data.nameImg.trim() !== '';

  wrapper.innerHTML = `
    <div class='columna imatge'>
      ${
        hasImage
          ? `
            <img src='https://media.elliot.cat/img/${data.tipusImatge}/${data.nameImg}.jpg'
                class='img-thumbnail'
                alt='Imatge'
                title='Imatge'>
            <p><span style="font-size:12px">${data.alt ?? ''}</span></p>
          `
          : ''
      }
    </div>

    <div class="columna">
      <div class="quadre-detalls">
        ${detailHtml}
      </div>
    </div>
  `;

  // Bloque de fechas (si existen)
  // Insertar fechas en el div#dadesFitxa si existe en el DOM
  if (dateCreated) {
    const dadesFitxaDiv = document.getElementById('dadesFitxa');
    if (dadesFitxaDiv) {
      const formattedCreated = formatDate(dateCreated);
      const formattedModified = dateModified ? formatDate(dateModified) : '';

      const shouldShowModified = dateModified && dateModified !== dateCreated && dateModified !== '0000-00-00' && formattedModified !== '00-00-0000';

      dadesFitxaDiv.innerHTML = `
      <strong>Aquesta fitxa ha estat creada el: </strong>
      <span>${formattedCreated}</span>
      ${shouldShowModified ? ` | <strong>Modificada el </strong>: <span>${formattedModified}</span>` : ''}
    `;
    }
  }

  if (descr) {
    const descrDiv = document.getElementById('dadesDescripcio');
    if (descrDiv) {
      descrDiv.innerHTML = `
      <h4>Més informació</h4>
      <span>${descr}</span>
    `;
      descrDiv.className = 'container';
      descrDiv.style.cssText = 'padding:20px;background-color:#ececec;margin-top:25px;margin-bottom:25px;';
    }
  }

  if (titol) {
    const titolDiv = document.getElementById('titolPagina');
    if (titolDiv) {
      titolDiv.innerHTML = `Fitxa: ${titol}`;
    }
  }

  return wrapper;
}
