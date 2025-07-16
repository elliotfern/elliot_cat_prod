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

    const data = await response.json();
    callback(data);

    // Asegúrate de que data sea un objeto o array adecuado
    const data2 = Array.isArray(data) ? data[0] : data;

    // Transformació de les dades
    // 1. Imatge
    const imgElement = document.getElementById('nameImg');
    const altElement = document.getElementById('alt');
    if (imgElement && altElement) {
      (imgElement as HTMLImageElement).src = `https://media.elliot.cat/img/${tipus}/${data.nameImg}.jpg`;
      altElement.innerHTML = `${data.alt}`;
    }

    const nomElement = document.getElementById('nom');
    if (nomElement) {
      (nomElement as HTMLElement).innerHTML = `${data.nom} ${data.cognoms}`;
    }

    // 2. Data creacio fitxa i actualitzacio
    const dateElement = document.getElementById('dateCreated');
    const dateElement2 = document.getElementById('dateModified');
    if (dateElement) {
      const dateObj = new Date(data.dateCreated);
      const day = dateObj.getDate();
      const month = dateObj.getMonth() + 1; // Los meses van de 0 a 11
      const year = dateObj.getFullYear();
      dateElement.textContent = `${day}/${month}/${year}`;
    }

    if (dateElement2) {
      const dateObj = new Date(data.dateModified);
      // Verifica si la fecha es válida
      if (data2['dateModified'] == '0000-00-00') {
        dateElement2.textContent = '';
      } else if (data2['dateModified'] == data2['dateCreated']) {
        dateElement2.textContent = '';
      } else {
        const day = dateObj.getDate();
        const month = dateObj.getMonth() + 1;
        const year = dateObj.getFullYear();
        dateElement2.innerHTML = `| <strong> Darrera modificació: </strong> ${day}/${month}/${year}`;
      }
    }

    // 3. Naixement
    const anyNaixement = parseInt(data.anyNaixement, 10);
    const diaNaixement = parseInt(data.diaNaixement);
    const mesNaixement = parseInt(data.mesNaixement);

    const anyDefuncio2 = data.anyDefuncio ? parseInt(data.anyDefuncio, 10) : null;
    const diaDefuncio = parseInt(data.diaDefuncio);
    const mesDefuncio = parseInt(data.mesDefuncio);

    // Verificamos si el día o el mes son 0 o null, y en ese caso asignamos un string vacío ""
    const diaMostrar = isNaN(diaNaixement) || diaNaixement === 0 || diaNaixement === null ? '' : diaNaixement.toString();
    const mesMostrar = isNaN(mesNaixement) || mesNaixement === 0 || mesNaixement === null ? '' : mesNaixement.toString();
    // Si tanto el día como el mes son válidos (mayores que 0), los incluimos en la fecha

    // Ahora verificamos si ambos valores (día y mes) son válidos antes de construir la fecha
    let dataNaixement = anyNaixement.toString(); // Por defecto solo mostramos el año
    if (diaMostrar && mesMostrar) {
      dataNaixement = `${diaMostrar} ${mesosCatala[parseInt(mesMostrar) - 1]} ${anyNaixement}`;
    }

    const anyDefuncio = parseInt(data.anyDefuncio, 10);
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
    const ciutatNaixement = data.ciutatNaixement ? ` (${data.ciutatNaixement})` : '';
    const ciutatDefuncio = data.ciutatDefuncio ? ` (${data.ciutatDefuncio})` : '';

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
        value: data.idSexe === 1 ? 'Home' : data.idSexe === 2 ? 'Dona' : 'Desconegut',
      },
      { label: 'Pais: ', value: data.pais_cat },
      { label: 'Professió: ', value: data.grup },
      { label: 'Pàgina Viquipèdia: ', value: `<a href="${data.web}" target="_blank" title="Web">Enllaç extern</a>` },
      { label: 'Biografia: ', value: data.descripcio || 'No disponible' }
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
