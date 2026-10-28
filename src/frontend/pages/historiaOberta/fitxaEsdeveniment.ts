import { mapEsdeveniment, mapEsdevenimentToFitxa } from '../../components/mappers/esdeveniment';
import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { Esdeveniment } from '../../types/Esdeveniment';
import { renderFitxa } from '../../utils/renderFitxa';
import { runFitxaEsdevenimentBlocks } from './fitxaEsdevenimentBlocs';

export async function fitxaEsdeveniment(url: string, slug: string): Promise<void> {
  try {
    // 1. API
    const esdevenimentApi = await api.get<Esdeveniment>(url, {
      slug,
    });

    // 2. VIEW NORMALITZADA
    const esdeveniment = mapEsdeveniment(esdevenimentApi);

    // 3. FITXA UI
    const fitxa = mapEsdevenimentToFitxa(esdeveniment);

    const isAdmin = await getIsAdmin();

    const fitxaOptions = {
      containerId: 'fitxaEsdeveniment',
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
              action: 'modifica-esdeveniment',
              id: esdeveniment.id,
              label: 'Modifica esdeveniment',
            },
          }
        : {}),
    };

    renderFitxa(fitxaOptions);
    // 4. BLOCS EXTRA
    await runFitxaEsdevenimentBlocks({
      containerSelector: '.quadre-extra',
      esdeveniment,
    });
  } catch (error) {
    console.error('Error carregant la fitxa de l’esdeveniment:', error);
  }
}
