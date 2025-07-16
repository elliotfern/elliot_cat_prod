// Definir los tipos de los parámetros
type CallbackFunction = (fila: any, columna: string) => string;

// Función para construir una tabla a partir de datos de una API
export async function construirTaula(taulaId: string, apiUrl: string, id: string, columnas: string[], callback: CallbackFunction): Promise<void> {
  try {
    // Construir la URL completa con el ID
    const url = apiUrl + id;

    // Realizar la solicitud a la API
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    });

    // Verificar si la respuesta fue correcta
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }

    // Parsear la respuesta a JSON
    const data = await response.json();

    // Accede a la propiedad 'data' que contiene los resultados
    const books = data.data;

    // Comprobar si no hay datos o si el array está vacío
    if (data.status === 'error' || books.length === 0) {
      const tablaContainer = document.getElementById(taulaId);
      if (tablaContainer) {
        tablaContainer.innerHTML = '<p>No hi ha cap informació disponible.</p>';
      }
      return; // Salir de la función
    }

    // Crear la tabla y su encabezado
    const table = document.createElement('table');
    table.classList.add('table', 'table-striped');

    const thead = document.createElement('thead');
    thead.classList.add('table-primary');
    const trHead = document.createElement('tr');
    columnas.forEach((columna) => {
      const th = document.createElement('th');
      th.textContent = columna;
      trHead.appendChild(th);
    });
    thead.appendChild(trHead);
    table.appendChild(thead);

    // Crear el cuerpo de la tabla
    const tbody = document.createElement('tbody');
    books.forEach((fila: any) => {
      const trBody = document.createElement('tr');
      columnas.forEach((columna) => {
        const td = document.createElement('td');
        td.innerHTML = callback(fila, columna);
        trBody.appendChild(td);
      });
      tbody.appendChild(trBody);
    });
    table.appendChild(tbody);

    // Agregar la tabla al contenedor deseado
    const tablaContainer = document.getElementById(taulaId);
    if (tablaContainer) {
      tablaContainer.innerHTML = ''; // Limpiar el contenido anterior
      tablaContainer.appendChild(table); // Añadir la nueva tabla
    }
  } catch (error) {
    console.error('Error en la solicitud:', error);
  }
}
