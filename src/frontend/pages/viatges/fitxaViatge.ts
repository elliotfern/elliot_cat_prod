import { renderFitxaInformacio } from '../../components/renderFitxaInformacio/renderFitxaInformacio';
import { getPageType } from '../../utils/urlPath';
import { formatDataCatala } from '../../utils/formataData';
import { api } from '../../core/api/client';
import { Viatge } from '../../types/Viatge';

export async function fitxaViatge() {
  const url = window.location.href;
  const pageType = getPageType(url);
  let viatge = pageType[3];

  let result: Viatge;
  try {
    result = await api.get<Viatge>(`viatges/get/fitxaViatgeDetalls`, {
      viatge,
    });
  } catch (error) {
    console.error(error);

    return;
  }

  const data = {
    nameImg: result.nameImg,
    alt: result.alt,
    tipusImatge: 'viatge-img',
    details: {
      Titol: result.viatge,
      Descripció: result.descripcio,
      País: result.pais_ca,
      'Data inici': formatDataCatala(result.dataInici),
      'Data fi': formatDataCatala(result.dataFi),
      'Data de creació': result.dateCreated,
      'Última modificació': result.dateModified,
    },
  };

  const container = document.getElementById('dadesContainer');
  if (container) {
    container.innerHTML = ''; // limpiar contenido previo
    container.appendChild(renderFitxaInformacio(data));
  }
}
