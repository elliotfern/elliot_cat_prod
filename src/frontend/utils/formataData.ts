export function formatData(inputDate: string): string {
  // Primero intentamos crear una fecha a partir del input
  let date = new Date(inputDate);

  // Si la fecha no es válida, intentamos otros formatos
  if (isNaN(date.getTime())) {
    // Intenta parsear formato YYYY/MM/DD
    date = new Date(inputDate.replace(/-/g, '/'));

    // Si sigue siendo inválido, intentamos un timestamp
    if (isNaN(date.getTime())) {
      return 'Data no vàlida';
    }
  }

  // Verifica si la fecha es de tipo '0000-00-00'
  if (inputDate === '0000-00-00') {
    return 'Data no vàlida';
  }

  // Extrae los componentes de la fecha
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();

  return `${day}-${month}-${year}`;
}

export function formatDataCatala(inputDate: string): string {
  // Array con los meses en catalán
  const mesesCatalan = ['gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];

  // Convertir la fecha
  let fecha = new Date(inputDate);
  let day = ('0' + fecha.getDate()).slice(-2); // Obtener el día y asegurar que tenga 2 dígitos
  let month = mesesCatalan[fecha.getMonth()]; // Obtener el nombre del mes en catalán
  let year = fecha.getFullYear(); // Obtener el año

  return `${day} ${month} ${year}`;
}

export function calculEdat(dataNaixement: string): string {
  // Validamos la fecha de nacimiento usando formatData
  const fechaFormateada = formatData(dataNaixement);

  // Si la fecha es inválida, devolvemos un mensaje de error
  if (fechaFormateada === 'Data no vàlida') {
    return 'Edat no disponible';
  }

  // Convertimos la fecha de nacimiento a un objeto Date
  const nacimiento = new Date(dataNaixement);

  // Obtenemos la fecha actual
  const hoy = new Date();

  // Calculamos la edad
  let edad = hoy.getFullYear() - nacimiento.getFullYear();

  // Ajustamos la edad si no ha pasado el cumpleaños este año
  const mesNacimiento = nacimiento.getMonth();
  const diaNacimiento = nacimiento.getDate();

  if (hoy.getMonth() < mesNacimiento || (hoy.getMonth() === mesNacimiento && hoy.getDate() < diaNacimiento)) {
    edad--;
  }

  return edad.toString();
}

export function formatNaixementEdat(edat: string): string {
  // Primero formateamos la fecha de nacimiento
  const fechaFormateada = formatDataCatala(edat);

  // Si la fecha es inválida, no mostramos nada
  if (fechaFormateada === 'Data no vàlida') {
    return '';
  }

  // Luego calculamos la edad
  const edad = calculEdat(edat);

  // Devolvemos la fecha de nacimiento y la edad en el formato deseado
  return `${fechaFormateada} (${edad} anys)`;
}
