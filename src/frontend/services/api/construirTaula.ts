// Definir los tipos de los parámetros
type CallbackFunction = (fila: any, columna: string) => string;
 
// Función para construir una tabla a partir de datos de una API
export function construirTaula(taulaId: string, apiUrl: string, id: string, columnas: string[], callback: CallbackFunction): void {
  // Construir la URL completa con el ID
  const url = apiUrl + id;

  // Realizar la solicitud a la API
  fetch(url, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then((data) => {
      // Comprobar si no hay datos o si el array está vacío "No rows"
      if (data.status === 'error') {
        // Si no hay datos, mostrar el mensaje
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
      data.forEach((fila: any) => {
        // Definir el tipo 'any' para 'fila' ya que no sabemos la estructura exacta
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
        tablaContainer.innerHTML = '';
        tablaContainer.appendChild(table);
      }
    })
    .catch((error) => {
      console.error('Error en la solicitud:', error);
    });
}
