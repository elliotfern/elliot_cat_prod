import { api } from '../../core/api/client';
import { renderFitxa } from '../../utils/renderFitxa';
import { mapLlibreToFitxa } from '../../components/mappers/llibre';
import { Llibre } from '../../types/Llibre';

export async function fitxaLlibre(baseUrl: string, llibre: string) {
  try {
    const data = await api.get<Llibre>(baseUrl, {
      llibre,
    });

    const fitxa = mapLlibreToFitxa(data);

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
        id: data.id,
        label: 'Modifica llibre',
      },
    });
  } catch (error) {
    console.error('Error fitxaLlibre:', error);
  }
}
