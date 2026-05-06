import { renderFitxaInformacio } from '../../components/renderFitxaInformacio/renderFitxaInformacio';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { formatDataCatala } from '../../utils/formataData';

export async function fitxaViatge() {
  const isAdmin = await getIsAdmin();
  const url = window.location.href;
  const pageType = getPageType(url);
  let slug: string = '';

  slug = pageType[3];

  const response = await fetch(`https://${window.location.host}/api/viatges/get/fitxaViatgeDetalls?viatge=${slug}`);
  const json = await response.json();
  const result = json.data[0]; // 👈 aquí está la clave

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
