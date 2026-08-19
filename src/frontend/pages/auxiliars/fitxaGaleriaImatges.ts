import { api } from '../../core/api/client';
import { DOMAIN_IMG } from '../../utils/urls';

interface ImatgeGaleria {
  id: string;
  nameImg: string;
  extension: string;
  typeImg: number;
  nom: string;
  alt: string | null;
  ordre: number;
}

interface GaleriaImatges {
  id: string;
  nom: string;
  directori: string;
  alt: string | null;
  dateCreated: string | null;
  dateModified: string | null;
  imatges: ImatgeGaleria[];
}

// ============================================================
// URL BASE DE LAS IMÁGENES
// ============================================================

const MEDIA_URL = `${DOMAIN_IMG}/img/galeria-imatges`;

// ============================================================
// FITXA GALERIA IMATGES
// ============================================================

export async function fitxaGaleriaImatge(id: string): Promise<void> {
  const container = document.getElementById('FitxaGaleriaImatges');

  if (!container) {
    return;
  }

  container.innerHTML = '';

  try {
    // ==========================================================
    // OBTENER GALERÍA
    // ==========================================================

    const data = await api.get<GaleriaImatges>('auxiliars/imatges/get/galeriaImatgesId', { id });

    // ==========================================================
    // ORDENAR IMÁGENES
    // ==========================================================

    const imatges = [...data.imatges].sort((a, b) => a.ordre - b.ordre);

    // ==========================================================
    // CONTENEDOR GALERÍA
    // ==========================================================

    const galeria = document.createElement('div');

    galeria.className = 'row g-3';

    // ==========================================================
    // SIN IMÁGENES
    // ==========================================================

    if (imatges.length === 0) {
      galeria.innerHTML = `
        <div class="col-12">
          <div class="alert alert-secondary">
            Aquesta galeria no conté cap imatge.
          </div>
        </div>
      `;

      container.appendChild(galeria);

      return;
    }

    // ==========================================================
    // IMÁGENES
    // ==========================================================

    imatges.forEach((imatge) => {
      const imageUrl = `${MEDIA_URL}/` + `${encodeURIComponent(data.directori)}/` + `${encodeURIComponent(imatge.nameImg)}.` + `${encodeURIComponent(imatge.extension)}`;

      const col = document.createElement('div');

      col.className = 'col-6 col-md-4 col-lg-3';

      col.innerHTML = `
        <div class="card h-100">

          <a
            href="${imageUrl}"
            class="galeria-imatge-link"
            data-bs-toggle="modal"
            data-bs-target="#modalGaleriaImatge"
            data-image="${imageUrl}"
            data-alt="${escapeHtmlAttribute(imatge.alt ?? imatge.nom)}"
          >

            <img
              src="${imageUrl}"
              alt="${escapeHtmlAttribute(imatge.alt ?? imatge.nom)}"
              class="card-img-top"
              style="
                height: 180px;
                object-fit: cover;
              "
            >

          </a>

          <div class="card-body">

            <h6 class="card-title mb-1">
              ${escapeHtml(imatge.nom)}
            </h6>

            ${
              imatge.alt
                ? `<p class="card-text small text-muted mb-0">
                    ${escapeHtml(imatge.alt)}
                  </p>`
                : ''
            }

          </div>

        </div>
      `;

      galeria.appendChild(col);
    });

    container.appendChild(galeria);

    // ==========================================================
    // MODAL
    // ==========================================================

    crearModalGaleria(container);

    // ==========================================================
    // EVENTOS MODAL
    // ==========================================================

    const modal = document.getElementById('modalGaleriaImatge');

    const modalImage = document.getElementById('modalGaleriaImatgeImg') as HTMLImageElement | null;

    if (!modal || !modalImage) {
      return;
    }

    const links = container.querySelectorAll('.galeria-imatge-link');

    links.forEach((link) => {
      link.addEventListener('click', () => {
        const imageUrl = link.getAttribute('data-image');
        const alt = link.getAttribute('data-alt');

        if (imageUrl) {
          modalImage.src = imageUrl;
          modalImage.alt = alt ?? '';
        }
      });
    });
  } catch (error) {
    console.error('Error obtenint la galeria:', error);

    container.innerHTML = `
      <div class="alert alert-danger">
        No s'ha pogut obtenir la galeria d'imatges.
      </div>
    `;
  }
}

// ============================================================
// CREAR MODAL
// ============================================================

function crearModalGaleria(container: HTMLElement): void {
  const modalExistente = document.getElementById('modalGaleriaImatge');

  if (modalExistente) {
    modalExistente.remove();
  }

  const modal = document.createElement('div');

  modal.id = 'modalGaleriaImatge';

  modal.className = 'modal fade';

  modal.tabIndex = -1;

  modal.setAttribute('aria-hidden', 'true');

  modal.innerHTML = `
    <div class="modal-dialog modal-xl modal-dialog-centered">

      <div class="modal-content">

        <div class="modal-header">

          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Tancar">
          </button>

        </div>

        <div class="modal-body text-center">

          <img
            id="modalGaleriaImatgeImg"
            src=""
            alt=""
            class="img-fluid"
            style="max-height: 80vh;"
          >

        </div>

      </div>

    </div>
  `;

  container.appendChild(modal);
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
