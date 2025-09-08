interface PersonaData {
  id2: string;
  anyNaixement: string;
  anyDefuncio: string | null;
  sexe: string;
  ciutatNaixement: string;
  ciutatDefuncio: string | null;
  pais_cat: string;
  professio_ca: string;
  web: string;
  descripcio: string;
  id: string;
  slug: string;
  grup_ids: string;
}

// Función para realizar la solicitud Axios a la API
export async function fitxaPersona(url: string, id: string, tipus: string, callback: (data: PersonaData) => void) {
  const urlAjax = `${url}${id}`;

  const mesosCatala = ['gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];

  try {
    const response = await fetch(urlAjax, {
      method: 'GET',
    });

    if (!response.ok) {
      throw new Error('Error en la sol·licitud AJAX');
    }

    const json = await response.json();

    callback(json);

    // aquí accedes directamente a la data de la persona
    const persona = json.data;

    // Transformació de les dades
    // 1. Imatge
    const imgElement = document.getElementById('nameImg');
    const altElement = document.getElementById('alt');
    if (imgElement && altElement) {
      (imgElement as HTMLImageElement).src = `https://media.elliot.cat/img/persona/${persona.img}.jpg`;
      altElement.innerHTML = `${persona.img}`;
    }

    const nomElement = document.getElementById('nom');
    if (nomElement) {
      (nomElement as HTMLElement).innerHTML = `${persona.nom} ${persona.cognoms}`;
    }

    // 2. Data creacio fitxa i actualitzacio
    const dateElement = document.getElementById('dateCreated');
    const dateElement2 = document.getElementById('dateModified');
    if (dateElement) {
      const dateObj = new Date(persona.dateCreated);
      const day = dateObj.getDate();
      const month = dateObj.getMonth() + 1; // Los meses van de 0 a 11
      const year = dateObj.getFullYear();
      dateElement.textContent = `${day}/${month}/${year}`;
    }

    if (dateElement2) {
      const dateObj = new Date(persona.dateModified);
      // Verifica si la fecha es válida
      if (persona['dateModified'] == '0000-00-00') {
        dateElement2.textContent = '';
      } else if (persona['dateModified'] == persona['dateCreated']) {
        dateElement2.textContent = '';
      } else {
        const day = dateObj.getDate();
        const month = dateObj.getMonth() + 1;
        const year = dateObj.getFullYear();
        dateElement2.innerHTML = `| <strong> Darrera modificació: </strong> ${day}/${month}/${year}`;
      }
    }

    // 3. Naixement
    const anyNaixement = parseInt(persona.anyNaixement, 10);
    const diaNaixement = parseInt(persona.diaNaixement);
    const mesNaixement = parseInt(persona.mesNaixement);

    const anyDefuncio2 = persona.anyDefuncio ? parseInt(persona.anyDefuncio, 10) : null;
    const diaDefuncio = parseInt(persona.diaDefuncio);
    const mesDefuncio = parseInt(persona.mesDefuncio);

    // Verificamos si el día o el mes son 0 o null, y en ese caso asignamos un string vacío ""
    const diaMostrar = isNaN(diaNaixement) || diaNaixement === 0 || diaNaixement === null ? '' : diaNaixement.toString();
    const mesMostrar = isNaN(mesNaixement) || mesNaixement === 0 || mesNaixement === null ? '' : mesNaixement.toString();
    // Si tanto el día como el mes son válidos (mayores que 0), los incluimos en la fecha

    // Ahora verificamos si ambos valores (día y mes) son válidos antes de construir la fecha
    let dataNaixement = anyNaixement.toString(); // Por defecto solo mostramos el año
    if (diaMostrar && mesMostrar) {
      dataNaixement = `${diaMostrar} ${mesosCatala[parseInt(mesMostrar) - 1]} ${anyNaixement}`;
    }

    const anyDefuncio = parseInt(persona.anyDefuncio, 10);
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
    if (anyDefuncio2) {
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
        label: 'Gènere: ',
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
