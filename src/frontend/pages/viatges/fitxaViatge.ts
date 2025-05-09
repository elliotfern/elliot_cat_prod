import { renderFitxaInformacio } from '../../components/renderFitxaInformacio/renderFitxaInformacio';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { formatDataCatala } from '../../utils/formataData';

export async function fitxaViatge() {
  const isAdmin = await getIsAdmin();
  const url = window.location.href;
  const pageType = getPageType(url);
  let slug: string = '';
  let gestioUrl: string = '';

  if (isAdmin) {
    slug = pageType[3];
    gestioUrl = '/gestio';
  } else {
    slug = pageType[2];
  }

  const response = await fetch(`https://${window.location.host}/api/viatges/get/?fitxaViatgeDetalls=${slug}`);
  const result = await response.json();

  const data = {
    nameImg: result.nameImg,
    alt: result.alt,
    tipusImatge: 'viatge-img',
    details: {
      Titol: result.viatge,
      Descripció: result.descripcio,
      País: result.pais_cat,
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
