import { mapSerieToFitxa } from '../../components/mappers/serie';
import { renderFitxa } from '../../utils/renderFitxa';

export async function fitxaSerie(baseUrl: string, slug: string) {
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
    const fitxa = mapSerieToFitxa(apiData);

    // 👉 2. render genérico
    renderFitxa({
      containerId: 'fitxaSerie',
      title: fitxa.title,
      image: fitxa.image,
      fields: fitxa.fields,
      description: fitxa.description,
      descriptionTitle: 'Crítica de la sèrie',
      dateCreated: fitxa.dateCreated,
      dateModified: fitxa.dateModified,
      editButton: {
        basePath: 'cinema',
        action: 'modifica-serie',
        id: apiData.slug,
        label: 'Modifica sèrie',
      },
    });
  } catch (error) {
    console.error('Error fitxaSerie:', error);
  }
}
