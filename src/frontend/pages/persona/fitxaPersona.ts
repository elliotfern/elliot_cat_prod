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
  idSexe: number | null;

  mes_naixement: number | null;
  dia_naixement: number | null;
  mes_defuncio: number | null;
  dia_defuncio: number | null;

  ciutatNaixement: string | null;
  ciutatDefuncio: string | null;
  idCiutatNaixement: number | null;
  idCiutatDefuncio: number | null;

  grup_ids: string[]; // ya viene como array
  grup: string | null; // "Autor/a"
}

// Shape “legacy” (lo que tu código está intentando usar)
interface PersonaLegacy {
  id: string;
  slug: string;

  nom: string;
  cognoms: string;

  web: string;
  descripcio: string;

  // imagen legacy
  img: string; // usas persona.img para construir URL

  // fechas legacy
  dateCreated: string | null;
  dateModified: string | null;

  // nacimiento/defunción legacy
  anyNaixement: string; // tu código hace parseInt(...)
  anyDefuncio: string | null;

  mesNaixement: string;
  diaNaixement: string;
  mesDefuncio: string;
  diaDefuncio: string;

  // campos legacy usados abajo
  genere: string; // en tu HTML lo pones como "Gènere"
  paisAutor: string; // en tu HTML lo pones como "Pais"

  ciutatNaixement: string | null;
  ciutatDefuncio: string | null;

  grup_ids: string[]; // mejor como array (no string)
}

function mapPersona(api: PersonaApiData): PersonaLegacy {
  return {
    id: api.id,
    slug: api.slug,

    nom: api.nom ?? '',
    cognoms: api.cognoms ?? '',

    web: api.web ?? '',
    descripcio: api.descripcio ?? '',

    // en tu API ahora viene nameImg (slug de imagen)
    img: api.nameImg ?? '',

    // en tu API son created_at / updated_at
    dateCreated: api.created_at ?? null,
    dateModified: api.updated_at ?? null,

    // tu código espera strings parseables
    anyNaixement: api.any_naixement != null ? String(api.any_naixement) : '',
    anyDefuncio: api.any_defuncio != null ? String(api.any_defuncio) : null,

    mesNaixement: api.mes_naixement != null ? String(api.mes_naixement) : '0',
    diaNaixement: api.dia_naixement != null ? String(api.dia_naixement) : '0',

    mesDefuncio: api.mes_defuncio != null ? String(api.mes_defuncio) : '0',
    diaDefuncio: api.dia_defuncio != null ? String(api.dia_defuncio) : '0',

    // antes tenías "genere" / "paisAutor"
    genere: api.grup ?? '', // si esto no es “género” realmente, cambia a lo que toque
    paisAutor: api.pais_ca ?? '',

    ciutatNaixement: api.ciutatNaixement ?? null,
    ciutatDefuncio: api.ciutatDefuncio ?? null,

    grup_ids: Array.isArray(api.grup_ids) ? api.grup_ids : [],
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
      altElement.innerHTML = `${persona.img}`;
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
    const anyNaixement = parseInt(persona.anyNaixement, 10);
    const diaNaixement = parseInt(persona.diaNaixement, 10);
    const mesNaixement = parseInt(persona.mesNaixement, 10);

    const anyDefuncio2 = persona.anyDefuncio ? parseInt(persona.anyDefuncio, 10) : null;
    const diaDefuncio = parseInt(persona.diaDefuncio, 10);
    const mesDefuncio = parseInt(persona.mesDefuncio, 10);

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

    const anyDefuncio: number | null = persona.anyDefuncio !== null ? parseInt(persona.anyDefuncio, 10) : null;
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
        label: 'Professió / grup: ',
        value: persona.genere,
      },
      { label: 'Pais: ', value: persona.paisAutor },
      { label: 'Pàgina Viquipèdia: ', value: `<a href="${persona.web}" target="_blank" title="Web">Enllaç extern</a>` },
      { label: 'Biografia: ', value: persona.descripcio || 'No disponible' }
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
  } catch (error) {
    console.error('Error al parsear JSON:', error); // Muestra el error de parsing
  }
}
