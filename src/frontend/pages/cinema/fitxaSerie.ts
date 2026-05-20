import { api } from '../../core/api/client';
import { mapSerieToFitxa } from '../../components/mappers/serie';
import { renderFitxa } from '../../utils/renderFitxa';
import { SerieTv } from '../../types/SerieTv';

export async function fitxaSerie(baseUrl: string, slug: string) {
  try {
    const json = await api.get<SerieTv>(baseUrl, {
      slug,
    });

    const fitxa = mapSerieToFitxa(json);

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
        id: '',
        label: 'Modifica sèrie',
      },
    });
  } catch (error) {
    console.error('Error fitxaSerie:', error);
  }
}
