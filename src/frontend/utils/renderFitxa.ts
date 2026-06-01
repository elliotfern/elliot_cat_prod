import { formatData } from './formataData';

type FitxaFieldType = 'text' | 'html' | 'link';

interface FitxaField {
  label: string;
  value: string;
  type?: FitxaFieldType;

  href?: string; // solo si type = link
}

interface FitxaImage {
  src: string;
  alt?: string;
}

interface RenderFitxaOptions {
  containerId: string;
  title: string;
  image?: FitxaImage;
  fields: FitxaField[];
  description?: string;
  descriptionTitle?: string;
  dateCreated: string;
  dateModified: string;
  editButton?: {
    basePath: string; // ej: "cinema", "persones"
    action: string; // ej: "modifica-pelicula", "modifica-persona"
    id: string; // id o slug
    label?: string; // opcional
  };
}

export function renderFitxa(options: RenderFitxaOptions) {
  const container = document.getElementById(options.containerId);

  if (!container) {
    console.error(`Container #${options.containerId} no trobat`);
    return;
  }

  container.innerHTML = `
    <div class="fitxa-wrapper">

    ${
      options.editButton
        ? `
            <div class="mb-3">
                <a
                href=/gestio/${options.editButton.basePath}/${options.editButton.action}/${options.editButton.id}/
                class="btn btn-secondary btn-sm"
                >
                ${options.editButton.label ?? 'Modifica fitxa'}
                </a>
            </div>
            `
        : ''
    }

         ${
           options.dateCreated
             ? `
            <div id=dadesCreacio" class="mt-3 text-muted small bg-light p-3 rounded mb-3 d-inline-block">

                <strong>Aquesta fitxa ha estat creada el: </strong>
                ${options.dateCreated}

                ${
                  options.dateModified
                    ? `
                    | <strong>Darrera modificació: </strong>
                    ${options.dateModified}
                    `
                    : ''
                }

            </div>
            `
             : ''
         }

      <div class="row g-4">

        <!-- IMATGE -->
        <div class="col-12 col-md-4 text-center">

          ${
            options.image
              ? `
                <img
                  src="${options.image.src}"
                  alt="${options.image.alt ?? options.title}"
                  title="${options.image.alt ?? options.title}"
                  class="img-fluid rounded shadow-sm border"
                >
              `
              : ''
          }

        </div>

        <!-- DETALLS -->
        <div class="col-12 col-md-8">

          <div class="card shadow-sm">

            <div class="card-body">

              <h2 class="mb-4">
                ${options.title}
              </h2>

              ${options.fields
                .map(
                  (field) => `
                    <div class="mb-2">
                      <strong>${field.label}:</strong>
                      ${field.type === 'link' ? `<a href="${field.href ?? '#'}">${field.value}</a>` : field.type === 'html' ? field.value : field.value}
                    </div>
                  `
                )
                .join('')}

            </div>

          </div>

        </div>

   </div>

      ${
        options.description
          ? `
            <div class="card mt-4 shadow-sm">

              <div class="card-body">

                <h4 class="mb-3">
                  ${options.descriptionTitle ?? 'Descripció'}
                </h4>

                <div>
                  ${options.description}
                </div>

              </div>

            </div>
          `
          : ''
      }

    </div>
  `;
}
