import { api } from '../../core/api/client';
import { DOMAIN_IMG } from '../../utils/urls';

interface Imatge {
  id: string;
  nameImg: string;
  extension: string;
  typeImg: number;
  nom: string;
  alt: string | null;
  any: string | null;
  dataImatge: string | null;
}

// ============================================================
// ESCAPAR HTML
// ============================================================

function escapeHtml(value: string): string {
  return value.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

// ============================================================
// ESCAPAR ATRIBUTO HTML
// ============================================================

function escapeHtmlAttribute(value: string): string {
  return escapeHtml(value);
}

// ============================================================
// MAPA TIPOS DE IMAGEN
// ============================================================

const IMAGE_TYPE_DIRECTORIES: Record<number, string> = {
  1: 'persona',
  2: 'biblioteca-llibre',
  3: 'historia-imatge',
  4: 'historia-esdeveniment',
  6: 'historia-organitzacio',
  7: 'cinema-serie',
  8: 'cinema-pelicula',
  10: 'historia-imatge-min',
  11: 'viatge',
  12: 'historia-mapa',
  13: 'blog',
  15: 'historia-infografia',
  16: 'historia-cronologia',
  17: 'viatge-espai',
  18: 'usuaris-avatar',
  19: 'web-icones',
  20: 'logos-empreses',
  22: 'galeria-imatges',
};

// ============================================================
// FITXA IMATGE
// ============================================================

interface Imatge {
  id: string;
  nameImg: string;
  extension: string;
  typeImg: number;
  nom: string;
  alt: string | null;
  any: string | null;
  dataImatge: string | null;
}

export async function fitxaImatge(id: string): Promise<void> {
  const container = document.getElementById('FitxaImatge');

  if (!container) {
    return;
  }

  container.innerHTML = '';

  try {
    // ==========================================================
    // OBTENER IMAGEN
    // ==========================================================

    const imatge = await api.get<Imatge>('auxiliars/imatges/get/imatgeId', { id });

    // ==========================================================
    // OBTENER DIRECTORIO SEGÚN TYPEIMG
    // ==========================================================

    const directory = IMAGE_TYPE_DIRECTORIES[imatge.typeImg];

    if (!directory) {
      container.innerHTML = `
        <div class="alert alert-danger">
          El tipus d'imatge no és vàlid.
        </div>
      `;

      return;
    }

    // ==========================================================
    // URL IMAGEN
    // ==========================================================

    const imageUrl = `${DOMAIN_IMG}/img/` + `${encodeURIComponent(directory)}/` + `${encodeURIComponent(imatge.nameImg)}.` + `${encodeURIComponent(imatge.extension)}`;

    // ==========================================================
    // FECHA / AÑO
    // ==========================================================

    let dataImatge = '';

    if (imatge.dataImatge) {
      const [year, month, day] = imatge.dataImatge.split(' ')[0].split('-');

      dataImatge = `${day}/${month}/${year}`;
    } else if (imatge.any) {
      dataImatge = imatge.any;
    }

    // ==========================================================
    // FICHA
    // ==========================================================

    const card = document.createElement('div');

    card.className = 'card';

    card.innerHTML = `

      <div class="row g-0">

        <!-- IMAGEN -->

        <div class="col-md-5">

          <a
            href="${imageUrl}"
            target="_blank"
            rel="noopener noreferrer"
          >

            <img
              src="${imageUrl}"
              alt="${escapeHtmlAttribute(imatge.alt ?? imatge.nom)}"
              class="img-fluid rounded-start"
              style="
                width: 100%;
                height: 100%;
                max-height: 500px;
                object-fit: contain;
              "
            >

          </a>

        </div>


        <!-- INFORMACIÓN -->

        <div class="col-md-7">

          <div class="card-body">

            <h5 class="card-title">
              ${escapeHtml(imatge.nom)}
            </h5>


            ${
              imatge.alt
                ? `
                  <p class="card-text">
                    ${escapeHtml(imatge.alt)}
                  </p>
                `
                : ''
            }


            ${
              dataImatge
                ? `
                  <p class="card-text small text-muted mb-0">
                    ${escapeHtml(dataImatge)}
                  </p>
                `
                : ''
            }

          </div>

        </div>

      </div>
    `;

    container.appendChild(card);
  } catch (error) {
    console.error('Error obtenint la imatge:', error);

    container.innerHTML = `
      <div class="alert alert-danger">
        No s'ha pogut obtenir la imatge.
      </div>
    `;
  }
}
