import { mapOrganitzacio, mapOrganitzacioToFitxa } from '../../components/mappers/organitzacio';
import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { Organitzacio } from '../../types/Organitzacio';
import { renderFitxa } from '../../utils/renderFitxa';

export async function fitxaOrganitzacio(url: string, slug: string): Promise<void> {
  try {
    // 1. API
    const organitzacioApi = await api.get<Organitzacio>(url, {
      slug,
    });

    // 2. VIEW NORMALITZADA
    const organitzacio = mapOrganitzacio(organitzacioApi);

    // 3. FITXA UI
    const fitxa = mapOrganitzacioToFitxa(organitzacio);

    const isAdmin = await getIsAdmin();

    const fitxaOptions = {
      containerId: 'fitxaOrganitzacio',
      title: fitxa.title,
      image: fitxa.image,
      fields: fitxa.fields,
      description: fitxa.description,
      dateCreated: fitxa.dateCreated,
      dateModified: fitxa.dateModified,
      ...(isAdmin
        ? {
            editButton: {
              basePath: 'historia',
              action: 'modifica-organitzacio',
              id: organitzacio.id,
              label: 'Modifica organització',
            },
          }
        : {}),
    };

    renderFitxa(fitxaOptions);
  } catch (error) {
    console.error('Error carregant la fitxa de l’organització:', error);
  }
}
