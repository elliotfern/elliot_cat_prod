import { mapPeliculaToFitxa } from '../../components/mappers/pelicula';
import { renderFitxa } from '../../utils/renderFitxa';

export async function fitxaPelicula(baseUrl: string, slug: string) {
  const urlAjax = `${baseUrl}${slug}`;

  try {
    const response = await fetch(urlAjax);

    if (!response.ok) {
      throw new Error('Error en la petició API');
    }

    const json = await response.json();

    if (json.status !== 'success' || !json.data?.length) {
      throw new Error('Dades no vàlides');
    }

    // ⚠️ tu API devuelve array con 1 elemento
    const apiData = json.data[0];

    // 👉 1. convertir API → modelo UI
    const fitxa = mapPeliculaToFitxa(apiData);

    // 👉 2. render genérico
    renderFitxa({
      containerId: 'fitxaPeli',
      title: fitxa.title,
      image: fitxa.image,
      fields: fitxa.fields,
      description: fitxa.description,
      descriptionTitle: 'Crítica de la pel·lícula',
      dateCreated: fitxa.dateCreated,
      dateModified: fitxa.dateModified,
      editButton: {
        basePath: 'cinema',
        action: 'modifica-pelicula',
        id: apiData.id,
        label: 'Modifica pel·lícula',
      },
    });
  } catch (error) {
    console.error('Error fitxaPeli:', error);
  }
}
