import { api } from '../../core/api/client';
import { mapPeliculaToFitxa } from '../../components/mappers/pelicula';
import { renderFitxa } from '../../utils/renderFitxa';

export async function fitxaPelicula(baseUrl: string, slug: string) {
  try {
    const pelicula = await api.get<Pelicula>(baseUrl, {
      slug,
    });

    const fitxa = mapPeliculaToFitxa(pelicula);

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
        id: pelicula.id,
        label: 'Modifica pel·lícula',
      },
    });
  } catch (error) {
    console.error('Error fitxaPeli:', error);
  }
}
