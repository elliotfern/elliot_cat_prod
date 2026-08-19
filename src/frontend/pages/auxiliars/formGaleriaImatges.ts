import { api } from '../../core/api/client';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
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
// FORMULARIO GALERÍA
// ============================================================

export async function formGaleriaImatges(isUpdate: boolean, id?: string): Promise<void> {
  const form = document.getElementById('galeriaImgForm') as HTMLFormElement | null;

  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;

  const btnSubmit = document.getElementById('btnForm') as HTMLButtonElement | null;

  const btnAfegirImatge = document.getElementById('btnAfegirImatge') as HTMLButtonElement | null;

  const imatgesContainer = document.getElementById('imatgesContainer') as HTMLDivElement | null;

  if (!form || !divTitol || !btnSubmit || !btnAfegirImatge || !imatgesContainer) {
    return;
  }

  // ============================================================
  // UPDATE
  // ============================================================

  if (isUpdate && id) {
    divTitol.innerHTML = '<h2>Modificació dades Galeria</h2>';

    btnSubmit.textContent = 'Modificar dades';

    try {
      const data = await api.get<GaleriaImatges>('auxiliars/imatges/get/galeriaImatgesId', { id });

      // ============================================================
      // ID DE LA GALERÍA
      // ============================================================

      let galleryIdInput = form.querySelector('input[name="id"]') as HTMLInputElement | null;

      if (!galleryIdInput) {
        galleryIdInput = document.createElement('input');
        galleryIdInput.type = 'hidden';
        galleryIdInput.name = 'id';
        form.appendChild(galleryIdInput);
      }

      galleryIdInput.value = id;

      // --------------------------------------------------------
      // Datos de la galería
      // --------------------------------------------------------

      const nomInput = document.getElementById('nom') as HTMLInputElement | null;

      const directoriInput = document.getElementById('directori') as HTMLInputElement | null;

      const altInput = document.getElementById('alt') as HTMLTextAreaElement | null;

      if (nomInput) {
        nomInput.value = data.nom;
      }

      if (directoriInput) {
        directoriInput.value = data.directori;
        directoriInput.disabled = true;
      }

      if (altInput) {
        altInput.value = data.alt ?? '';
      }

      // --------------------------------------------------------
      // Imágenes existentes
      // --------------------------------------------------------

      imatgesContainer.innerHTML = '';

      // Mantener el orden de la galería

      data.imatges
        .sort((a, b) => a.ordre - b.ordre)
        .forEach((imatge) => {
          crearBloqueImatgeExistente(imatgesContainer, imatge, data.directori);
        });

      // --------------------------------------------------------
      // Añadir nuevas imágenes
      // --------------------------------------------------------

      btnAfegirImatge.addEventListener('click', () => {
        crearBloqueImatge(imatgesContainer);
      });

      // --------------------------------------------------------
      // Submit UPDATE
      // --------------------------------------------------------

      form.addEventListener('submit', (event) => {
        transmissioDadesDB(event, 'POST', 'galeriaImgForm', API_URLS.PUT.GALERIA_IMATGES, true, 'none', undefined, true);
      });
    } catch (error) {
      console.error('Error obtenint la galeria:', error);
    }

    return;
  }

  // ============================================================
  // CREATE
  // ============================================================

  divTitol.innerHTML = '<h2>Alta nova galeria</h2>';

  btnSubmit.textContent = 'Crear galeria';

  // Primer bloque de imagen

  crearBloqueImatge(imatgesContainer);

  // Añadir imágenes

  btnAfegirImatge.addEventListener('click', () => {
    crearBloqueImatge(imatgesContainer);
  });

  // Submit CREATE

  form.addEventListener('submit', (event) => {
    transmissioDadesDB(event, 'POST', 'galeriaImgForm', API_URLS.POST.GALERIA_IMATGES);
  });
}

// ============================================================
// BLOQUE IMAGEN EXISTENTE
// ============================================================

function crearBloqueImatgeExistente(container: HTMLDivElement, imatge: ImatgeGaleria, directori: string): void {
  const index = container.children.length;

  const bloque = document.createElement('div');

  bloque.className = 'col-12';

  // ------------------------------------------------------------
  // URL imagen
  // ------------------------------------------------------------

  const imageUrl = `${MEDIA_URL}/` + `${encodeURIComponent(directori)}/` + `${encodeURIComponent(imatge.nameImg)}.` + `${imatge.extension}`;

  bloque.innerHTML = `

    <div class="card">

      <div class="card-header d-flex justify-content-between align-items-center">

        <strong>
          Imatge ${index + 1}
        </strong>

        <button
          type="button"
          class="btn btn-sm btn-outline-danger btnEliminarImatge">
          Eliminar
        </button>

      </div>


      <div class="card-body row g-3">


        <!-- ================================================== -->
        <!-- ID IMAGEN -->
        <!-- ================================================== -->

        <input
          type="hidden"
          name="imatges[${index}][id]"
          value="${escapeHtmlAttribute(imatge.id)}">


        <!-- ================================================== -->
        <!-- MINIATURA -->
        <!-- ================================================== -->

        <div class="col-12">

          <div class="small text-muted mb-2">
            Imatge actual:
          </div>

          <div>

            <img
              src="${imageUrl}"
              alt="${escapeHtmlAttribute(imatge.alt ?? imatge.nom)}"
              class="img-thumbnail"
              style="
                max-width: 250px;
                max-height: 180px;
                object-fit: contain;
              ">

          </div>

        </div>


        <!-- ================================================== -->
        <!-- ARCHIVO -->
        <!-- ================================================== -->

        <div class="col-12">

          <div class="small text-muted">
            Fitxer actual:
          </div>

          <div>
            ${escapeHtml(imatge.nameImg)}.${escapeHtml(imatge.extension)}
          </div>

        </div>


        <!-- ================================================== -->
        <!-- NOMBRE -->
        <!-- ================================================== -->

        <div class="col-md-6">

          <label
            for="imatge-${index}-nom"
            class="form-label">

            Nom
            <span class="text-danger">*</span>

          </label>

          <input
            type="text"
            class="form-control"
            id="imatge-${index}-nom"
            name="imatges[${index}][nom]"
            value="${escapeHtmlAttribute(imatge.nom)}"
            required>

        </div>


        <!-- ================================================== -->
        <!-- ALT -->
        <!-- ================================================== -->

        <div class="col-12">

          <label
            for="imatge-${index}-alt"
            class="form-label">

            Descripció de la imatge

          </label>

          <textarea
            class="form-control"
            id="imatge-${index}-alt"
            name="imatges[${index}][alt]"
            rows="3">${escapeHtml(imatge.alt ?? '')}</textarea>

        </div>


      </div>

    </div>

  `;

  container.appendChild(bloque);

  // ------------------------------------------------------------
  // Eliminar
  // ------------------------------------------------------------

  const btnEliminar = bloque.querySelector('.btnEliminarImatge') as HTMLButtonElement | null;

  btnEliminar?.addEventListener('click', () => {
    bloque.remove();

    actualizarNumeracionImatges(container);
  });
}

// ============================================================
// BLOQUE IMAGEN NUEVA
// ============================================================

function crearBloqueImatge(container: HTMLDivElement): void {
  const index = container.children.length;

  const bloque = document.createElement('div');

  bloque.className = 'col-12';

  bloque.innerHTML = `

    <div class="card">

      <div class="card-header d-flex justify-content-between align-items-center">

        <strong>
          Imatge ${index + 1}
        </strong>

        <button
          type="button"
          class="btn btn-sm btn-outline-danger btnEliminarImatge">
          Eliminar
        </button>

      </div>


      <div class="card-body row g-3">


        <!-- ================================================== -->
        <!-- ARCHIVO -->
        <!-- ================================================== -->

        <div class="col-md-6">

          <label
            for="imatge-${index}-file"
            class="form-label">

            Fitxer
            <span class="text-danger">*</span>

          </label>

          <input
            type="file"
            class="form-control"
            id="imatge-${index}-file"
            name="imatges[${index}][file]"
            accept="image/jpeg,image/png"
            required>

        </div>


        <!-- ================================================== -->
        <!-- NOMBRE -->
        <!-- ================================================== -->

        <div class="col-md-6">

          <label
            for="imatge-${index}-nom"
            class="form-label">

            Nom
            <span class="text-danger">*</span>

          </label>

          <input
            type="text"
            class="form-control"
            id="imatge-${index}-nom"
            name="imatges[${index}][nom]"
            required>

        </div>


        <!-- ================================================== -->
        <!-- ALT -->
        <!-- ================================================== -->

        <div class="col-12">

          <label
            for="imatge-${index}-alt"
            class="form-label">

            Descripció de la imatge

          </label>

          <textarea
            class="form-control"
            id="imatge-${index}-alt"
            name="imatges[${index}][alt]"
            rows="3"></textarea>

        </div>


      </div>

    </div>

  `;

  container.appendChild(bloque);

  // ------------------------------------------------------------
  // Eliminar
  // ------------------------------------------------------------

  const btnEliminar = bloque.querySelector('.btnEliminarImatge') as HTMLButtonElement | null;

  btnEliminar?.addEventListener('click', () => {
    bloque.remove();

    actualizarNumeracionImatges(container);
  });
}

// ============================================================
// ACTUALIZAR NUMERACIÓN E ÍNDICES
// ============================================================

function actualizarNumeracionImatges(container: HTMLDivElement): void {
  const blocs = container.children;

  Array.from(blocs).forEach((bloc, index) => {
    const card = bloc.querySelector('.card');

    if (!card) {
      return;
    }

    // --------------------------------------------------------
    // Título
    // --------------------------------------------------------

    const header = card.querySelector('.card-header strong');

    if (header) {
      header.textContent = `Imatge ${index + 1}`;
    }

    // --------------------------------------------------------
    // Actualizar names
    // --------------------------------------------------------

    const inputs = card.querySelectorAll('[name]');

    inputs.forEach((element) => {
      const input = element as HTMLInputElement | HTMLTextAreaElement;

      const name = input.getAttribute('name');

      if (!name) {
        return;
      }

      input.setAttribute('name', name.replace(/^imatges\[\d+\]/, `imatges[${index}]`));
    });

    // --------------------------------------------------------
    // Actualizar IDs
    // --------------------------------------------------------

    const elementsWithId = card.querySelectorAll('[id]');

    elementsWithId.forEach((element) => {
      const htmlElement = element as HTMLElement;

      const currentId = htmlElement.id;

      htmlElement.id = currentId.replace(/imatge-\d+/, `imatge-${index}`);
    });

    // --------------------------------------------------------
    // Actualizar "for" de labels
    // --------------------------------------------------------

    const labels = card.querySelectorAll('label[for]');

    labels.forEach((label) => {
      const htmlLabel = label as HTMLLabelElement;

      htmlLabel.htmlFor = htmlLabel.htmlFor.replace(/imatge-\d+/, `imatge-${index}`);
    });
  });
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
