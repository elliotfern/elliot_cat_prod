import { api } from '../../core/api/client';
import { renderFitxa } from '../../utils/renderFitxa';
import { mapLlibreToFitxa } from '../../components/mappers/llibre';
import { Llibre } from '../../types/Llibre';

export async function fitxaLlibre(baseUrl: string, slug: string) {
  try {
    const llibre = await api.get<Llibre>(baseUrl, {
      slug,
    });

    const fitxa = mapLlibreToFitxa(llibre);

    renderFitxa({
      containerId: 'fitxaLlibre',
      title: fitxa.title,
      image: fitxa.image,
      fields: fitxa.fields,
      description: fitxa.description,
      descriptionTitle: 'Crítica del llibre',
      dateCreated: fitxa.dateCreated,
      dateModified: fitxa.dateModified,
      editButton: {
        basePath: 'biblioteca',
        action: 'modifica-llibre',
        id: llibre.id,
        label: 'Modifica llibre',
      },
    });
  } catch (error) {
    console.error('Error fitxaLlibre:', error);
  }
}
