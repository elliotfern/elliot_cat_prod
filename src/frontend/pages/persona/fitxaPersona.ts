// Respuesta genérica de la API
interface ApiResponse<T> {
  status: 'success' | 'error';
  message: string;
  errors: any[];
  data: T;
}

// Lo que viene en json.data (según tu ejemplo)
interface PersonaApiData {
  id: string;
  cognoms: string;
  nom: string;

  pais_ca: string | null;
  idPais: number | null;

  any_naixement: number | null;
  any_defuncio: number | null;

  nameImg: string | null;
  alt: string | null;

  web: string | null;
  created_at: string | null; // "YYYY-MM-DD HH:MM:SS.ffffff"
  updated_at: string | null;
  descripcio: string | null;

  slug: string;
  idImg: number | null;

  mes_naixement: number | null;
  dia_naixement: number | null;
  mes_defuncio: number | null;
  dia_defuncio: number | null;

  ciutatNaixement: string | null;
  ciutatDefuncio: string | null;
  idCiutatNaixement: number | null;
  idCiutatDefuncio: number | null;

  grups: { id: string; nom: string }[];
  sexe_id: number | null;
}

interface PersonaView {
  id: string;
  slug: string;

  nom: string;
  cognoms: string;

  img: string;
  alt: string;

  web: string;
  descripcio: string;

  dateCreated: string | null;
  dateModified: string | null;

  anyNaixement?: number | null;
  anyDefuncio?: number | null;

  mesNaixement?: number | null;
  diaNaixement?: number | null;

  mesDefuncio?: number | null;
  diaDefuncio?: number | null;

  ciutatNaixement: string | null;
  ciutatDefuncio: string | null;

  paisAutor: string;

  sexe: string; // ✅ separado
  grupsText: string; // ✅ separado
}

function mapPersona(api: PersonaApiData): PersonaView {
  return {
    id: api.id,
    slug: api.slug,

    nom: api.nom ?? '',
    cognoms: api.cognoms ?? '',

    img: api.nameImg ?? '',
    alt: api.alt ?? '',

    web: api.web ?? '',
    descripcio: api.descripcio ?? '',

    dateCreated: api.created_at ?? null,
    dateModified: api.updated_at ?? null,

    anyNaixement: api.any_naixement ?? null,
    anyDefuncio: api.any_defuncio ?? null,

    mesNaixement: api.mes_naixement ?? null,
    diaNaixement: api.dia_naixement ?? null,

    mesDefuncio: api.mes_defuncio ?? null,
    diaDefuncio: api.dia_defuncio ?? null,

    ciutatNaixement: api.ciutatNaixement ?? null,
    ciutatDefuncio: api.ciutatDefuncio ?? null,

    paisAutor: api.pais_ca ?? '',

    // ✅ SEXO correcto
    sexe: api.sexe_id === 1 ? 'Home' : api.sexe_id === 2 ? 'Dona' : '',

    // ✅ GRUPOS correctos
    grupsText: Array.isArray(api.grups) ? api.grups.map((g) => g.nom).join(', ') : '',
  };
}

export async function fitxaPersona(url: string, id: string, tipus: string, callback: (persona: PersonaApiData) => void) {
  const urlAjax = `${url}${id}`;
  const mesosCatala = ['gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];

  try {
    const response = await fetch(urlAjax, { method: 'GET' });
    if (!response.ok) throw new Error('Error en la sol·licitud AJAX');

    const json: ApiResponse<PersonaApiData> = await response.json();
    callback(json.data); // 👈 solo data

    // mapper a legacy
    const persona = mapPersona(json.data);

    // 1. Imatge
    const imgElement = document.getElementById('nameImg');
    const altElement = document.getElementById('alt');
    if (imgElement && altElement) {
      (imgElement as HTMLImageElement).src = `https://media.elliot.cat/img/persona/${persona.img}.jpg`;
      altElement.innerHTML = `${persona.alt}`;
    }

    const nomElement = document.getElementById('nom');
    if (nomElement) (nomElement as HTMLElement).innerHTML = `${persona.nom} ${persona.cognoms}`;

    // 2. Data creacio fitxa i actualitzacio
    const dateElement = document.getElementById('dateCreated');
    const dateElement2 = document.getElementById('dateModified');

    if (dateElement && persona.dateCreated) {
      const dateObj = new Date(persona.dateCreated);
      const day = dateObj.getDate();
      const month = dateObj.getMonth() + 1;
      const year = dateObj.getFullYear();
      dateElement.textContent = `${day}/${month}/${year}`;
    }

    if (dateElement2 && persona.dateModified && persona.dateCreated) {
      const dateObj = new Date(persona.dateModified);
      if (persona.dateModified === '0000-00-00' || persona.dateModified === persona.dateCreated) {
        dateElement2.textContent = '';
      } else {
        const day = dateObj.getDate();
        const month = dateObj.getMonth() + 1;
        const year = dateObj.getFullYear();
        dateElement2.innerHTML = `| <strong> Darrera modificació: </strong> ${day}/${month}/${year}`;
      }
    }

    // 3. Naixement (tu código ya funciona con el mapper)
    const anyNaixement = persona.anyNaixement ?? 0;
    const diaNaixement = persona.diaNaixement ?? 0;
    const mesNaixement = persona.mesNaixement ?? 0;

    const anyDefuncio2 = persona.anyDefuncio ?? 0;
    const diaDefuncio = persona.diaDefuncio ?? 0;
    const mesDefuncio = persona.mesDefuncio ?? 0;

    // ... el resto de tu función igual (persona.genere, persona.paisAutor, etc.)
    // Verificamos si el día o el mes son 0 o null, y en ese caso asignamos un string vacío ""
    const diaMostrar = isNaN(diaNaixement) || diaNaixement === 0 || diaNaixement === null ? '' : diaNaixement.toString();
    const mesMostrar = isNaN(mesNaixement) || mesNaixement === 0 || mesNaixement === null ? '' : mesNaixement.toString();
    // Si tanto el día como el mes son válidos (mayores que 0), los incluimos en la fecha

    // Ahora verificamos si ambos valores (día y mes) son válidos antes de construir la fecha
    let dataNaixement = anyNaixement.toString(); // Por defecto solo mostramos el año
    if (diaMostrar && mesMostrar) {
      dataNaixement = `${diaMostrar} ${mesosCatala[parseInt(mesMostrar) - 1]} ${anyNaixement}`;
    }

    const anyDefuncio = persona.anyDefuncio ?? 0;
    const anyActual = new Date().getFullYear();

    // calcul de l'edat

    let edad;

    const fechaNacimiento = new Date(anyNaixement, mesNaixement - 1, diaNaixement); // mesNaixement - 1 porque los meses en JS empiezan desde 0
    const fechaDefuncion = anyDefuncio ? new Date(anyDefuncio, mesDefuncio - 1, diaDefuncio) : null; // Lo mismo para la defunción

    // Calculamos la fecha actual
    const fechaActual = new Date();
    const mesActual = fechaActual.getMonth(); // Los meses en JS van de 0 (enero) a 11 (diciembre)
    const diaActual = fechaActual.getDate();

    // Si no hay fecha de defunción, calculamos la edad con la fecha actual
    if (!anyDefuncio) {
      edad = anyActual - anyNaixement; // Edad base solo con el año

      // Verificamos si el cumpleaños ya pasó este año
      if (mesActual < fechaNacimiento.getMonth() || (mesActual === fechaNacimiento.getMonth() && diaActual < fechaNacimiento.getDate())) {
        edad--; // Si no ha pasado el cumpleaños, restamos un año
      }
    } else {
      // Si hay fecha de defunción, calculamos la edad con la fecha de defunción
      edad = anyDefuncio - anyNaixement; // Edad base solo con el año

      // Verificamos si la persona ya había cumplido años en la fecha de la defunción
      if (mesDefuncio < fechaNacimiento.getMonth() || (mesDefuncio === fechaNacimiento.getMonth() && diaDefuncio < diaNaixement)) {
        edad--; // Si no había cumplido años antes de morir, restamos un año
      }
    }

    // 4. Defunció
    // Verificar si el día o mes de defunción son válidos
    const diaMostrarDefuncio = isNaN(diaDefuncio) || diaDefuncio === 0 || diaDefuncio === null ? '' : diaDefuncio.toString();
    const mesMostrarDefuncio = isNaN(mesDefuncio) || mesDefuncio === 0 || mesDefuncio === null ? '' : mesDefuncio.toString();

    // Definir la variable `dataDefuncio`
    let dataDefuncio = '';
    if (anyDefuncio2 && anyDefuncio) {
      dataDefuncio = anyDefuncio.toString(); // Mostrar solo el año por defecto
      if (diaMostrarDefuncio && mesMostrarDefuncio) {
        dataDefuncio = `${diaMostrarDefuncio} ${mesosCatala[parseInt(mesMostrarDefuncio) - 1]} ${anyDefuncio2}`; // Añadir día y mes si existen
      }
    }

    // 5. Ciutats
    const ciutatNaixement = persona.ciutatNaixement ? ` (${persona.ciutatNaixement})` : '';
    const ciutatDefuncio = persona.ciutatDefuncio ? ` (${persona.ciutatDefuncio})` : '';

    // Ara injectem tota la informació al div "quadre-detalls"
    const quadreDetalls = document.querySelector('.quadre-detalls') as HTMLElement;

    // Limpiar cualquier contenido previo
    quadreDetalls.innerHTML = '';

    const parrafosHTML: { label: string; value: string }[] = [];

    // Primero Naixement
    parrafosHTML.push({
      label: 'Naixement: ',
      value: `${dataNaixement} ${ciutatNaixement}` + (!anyDefuncio ? ` - ${edad} anys` : ''),
    });

    // Condicionalmente agregar la entrada de defunción si existe
    if (anyDefuncio) {
      // Si hay fecha de defunción, mostramos la etiqueta
      parrafosHTML.push({
        label: 'Defunció: ',
        value: `${dataDefuncio} ${ciutatDefuncio} - ${edad} anys`, // Mostramos la fecha y edad
      });
    } else {
      // Si no hay fecha de defunción, no agregamos la etiqueta
    }

    // Luego el resto
    parrafosHTML.push(
      {
        label: 'Sexe: ',
        value: persona.sexe,
      },
      {
        label: 'Professió / grup: ',
        value: persona.grupsText,
      },
      {
        label: 'Pais/Nacionalitat: ',
        value: persona.paisAutor,
      },
      {
        label: 'Pàgina Viquipèdia: ',
        value: `<a href="${persona.web}" target="_blank">Enllaç extern</a>`,
      },
      {
        label: 'Biografia: ',
        value: persona.descripcio || 'No disponible',
      }
    );

    // Recorremos el array y agregamos cada párrafo al div
    parrafosHTML.forEach((item) => {
      const p = document.createElement('p');
      const strong = document.createElement('strong');
      strong.textContent = item.label;
      const span = document.createElement('span');
      span.innerHTML = item.value; // Usamos innerHTML para insertar HTML en el caso del link
      p.appendChild(strong);
      p.appendChild(span);
      quadreDetalls.appendChild(p);
    });

    const quadreProfessio = document.querySelector('.quadre-professio') as HTMLElement;

    if (quadreProfessio) {
      quadreProfessio.innerHTML = '';

      const grups = persona.grupsText.split(',').map((g) => g.trim());

      for (const grup of grups) {
        const content = await renderProfessioBlock(grup, persona);

        if (content) {
          if (typeof content === 'string') {
            quadreProfessio.innerHTML += content;
          } else {
            quadreProfessio.appendChild(content);
          }
        }
      }
    }
  } catch (error) {
    console.error('Error al parsear JSON:', error); // Muestra el error de parsing
  }
}

async function renderProfessioBlock(grup: string, persona: PersonaView) {
  switch (grup) {
    case 'Actor/a':
    // return renderActor(persona);

    case 'Historiador/a':
      return renderHistoriador(persona);

    default:
      return null;
  }
}

async function renderHistoriador(persona: PersonaView) {
  const res = await fetch(`/api/biblioteca/get/autorLlibres?id=${persona.id}`);

  if (!res.ok) return null;

  const json = await res.json();

  if (json.status !== 'success' || !Array.isArray(json.data)) return null;

  const llibres = json.data;

  const wrapper = document.createElement('div');

  wrapper.innerHTML = `
    <hr class="my-4">

    <h4 class="mb-3">📚 Llibres publicats</h4>

    <div class="table-responsive">
      <table class="table table-sm table-striped table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>Títol</th>
            <th>Any</th>
            <th class="text-end">Accions</th>
          </tr>
        </thead>

        <tbody>
          ${llibres
            .map(
              (l: any) => `
            <tr>
              <td>
                <a href="https://elliot.cat/gestio/biblioteca/fitxa-llibre/${l.slug}">
                  ${l.titol}
                </a>
              </td>

              <td>${l.any ?? ''}</td>

              <td class="text-end">
                <a 
                  href="https://elliot.cat/gestio/biblioteca/modifica-llibre/${l.slug}"
                  class="btn btn-sm btn-outline-warning"
                >
                  Modifica
                </a>
              </td>
            </tr>
          `
            )
            .join('')}
        </tbody>
      </table>
    </div>
  `;

  return wrapper;
}
