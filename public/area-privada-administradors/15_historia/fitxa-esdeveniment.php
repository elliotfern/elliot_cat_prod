<?php
$slug = $routeParams[0];
?>

<div class="container">
    <div class="barraNavegacio">
        <h6><a href="<?php echo APP_INTRANET; ?>">Intranet</a> > <a href="<?php echo APP_INTRANET . $url['historia']; ?>">Història</a> > <a href="<?php echo APP_INTRANET . $url['historia']; ?>/llistat-esdeveniments">LListat esdeveniments històrics</a> </h6>
    </div>

    <main>
        <div class="container contingut">
            <h1>Esdeveniment: <span id="nom"></span></h1>

            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['historia']; ?>/modifica-esdeveniment/<?php echo $slug; ?>'" class="button btn-gran btn-secondari">Modifica fitxa</button>

            <div class="dadesFitxa">
                <span id="dateCreated"></span> <span id="dateModified"></span>
            </div>

            <div class='fixaDades'>

                <div class='columna imatge'>
                    <img id="nameImg" src='' class='img-thumbnail' alt='Imatge' title='Imatge'>
                    <p><span id="alt" style="font-size:12px"></span></p>
                </div>

                <div class="columna">
                    <div class="quadre-detalls">
                        <p><strong>Data: </strong> <span id="data"></span></p>
                        <p><strong>Etapa històrica: </strong> <span id="etapaNom"></span></p>
                        <p><strong>Sub-etapa: </strong> <span id="nomSubEtapa"></span></p>
                        <p><strong>Ciutat: </strong> <span id="ciutat"></span></p>
                        <p><strong>País: </strong> <span id="pais_cat"></span></p>
                    </div>
                </div>
            </div>

            <hr>
            <div class="container" style="padding:20px;background-color:#ececec;margin-top:25px;margin-bottom:25px">
                <h4>Descripció dels esdeveniments:</h4>
                <span id="descripcio"></span>
            </div>

            <hr>
            <h4>Persones vinculades a l'esdeveniment:</h4>
            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['historia']; ?>/modifica-esdeveniment-persona/<?php echo $slug; ?>'" class="button btn-gran btn-secondari">Afegir persones a l'esdeveniment</button>

            <div class="table-responsive">
                <table id="taula1" class="table table-striped"></table>
            </div>

            <hr>
            <h4>Organitzacions vinculades a l'esdeveniment històric:</h4>
            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['historia']; ?>/modifica-esdeveniment-organitzacio/<?php echo $slug; ?>'" class="button btn-gran btn-secondari">Afegir organitzacions a l'esdeveniment</button>

            <div class="table-responsive">
                <table id="taula2" class="table table-striped"></table>
            </div>

        </div>

    </main>
</div>


<script>
    // Función para realizar la solicitud a la API
    function fetchApiData(url) {
        fetch(url, {
                method: "GET",
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data); // Ver los datos recibidos
                // Procesar la respuesta JSON
                if (Array.isArray(data) && data.length > 0) {
                    data = data[0];
                }
                try {

                    // transformació i verificacio de dates
                    let fechaFormateada = 'format de data no vàlid.';
                    let fechaFormateada2 = '';

                    if (data.dateCreated && data.dateCreated !== '0000-00-00') {
                        const fecha = new Date(data.dateCreated);
                        if (!isNaN(fecha)) {
                            fechaFormateada = fecha.toLocaleDateString('es-ES').replace(/\//g, "-");
                        }
                    }

                    if (data.dateModified && data.dateModified !== '0000-00-00') {
                        const fecha2 = new Date(data.dateModified);
                        if (!isNaN(fecha2)) {
                            fechaFormateada2 = fecha2.toLocaleDateString('es-ES').replace(/\//g, "-");
                        }
                    }

                    // Actualizar el DOM con los datos recibidos
                    document.getElementById('nom').textContent = data.esdeNom;
                    document.getElementById('nameImg').src = `https://media.elliot.cat/img/historia-esdeveniment/${data.nameImg}.jpg`;
                    document.getElementById('nomSubEtapa').textContent = data.nomSubEtapa;
                    document.getElementById('ciutat').textContent = data.ciutat;
                    document.getElementById('etapaNom').textContent = data.etapaNom;
                    document.getElementById('pais_cat').textContent = data.pais_cat;
                    document.getElementById('descripcio').textContent = data.descripcio;
                    document.getElementById('alt').textContent = data.alt;

                    const dataInici = formatData(data.esdeDataIDia, data.esdeDataIMes, data.esdeDataIAny);
                    const dataFi = formatData(data.esdeDataFDia, data.esdeDataFMes, data.esdeDataFAny);

                    let dataFinal = dataInici;
                    if (dataFi && dataFi !== dataInici) {
                        dataFinal += ` - ${dataFi}`;
                    }

                    document.getElementById('data').textContent = dataFinal;
                    const dateElement = document.getElementById('dateCreated');
                    const dateElement2 = document.getElementById('dateModified');
                    dateElement.innerHTML = `<strong>Aquesta fitxa ha estat creada el: </strong> ${fechaFormateada}`;

                    // Verifica si la fecha es válida
                    if (fechaFormateada2 == '0000-00-00' || fechaFormateada2 === '') {
                        dateElement2.textContent = ''; // No mostrar nada si no es válida
                    } else if (fechaFormateada == fechaFormateada2) {
                        dateElement2.textContent = '';
                    } else {
                        dateElement2.innerHTML = `| <strong> Darrera modificació: </strong> ${fechaFormateada2}`;

                    }

                    obtenerDatos("/api/historia/get/?personesEsdeveniments=" + data.id, "taula1", "Nom i cognoms", "persona", "persona");
                    obtenerDatos("/api/historia/get/?organitzacionsEsdeveniments=" + data.id, "taula2", "Organització", "organitzacio", "organitzacio");


                } catch (error) {
                    console.error('Error al parsear JSON:', error);
                }
            })
            .catch(error => {
                console.error('Error en la solicitud:', error);
            });
    }

    // Llamar a la función fetchApiData con la URL de la API y el slug del libro
    fetchApiData("/api/historia/get/?esdeveniment=<?php echo $slug; ?>");

    function getMesCatalan(numero) {
        const mesos = [
            "", "gener", "febrer", "març", "abril", "maig", "juny",
            "juliol", "agost", "setembre", "octubre", "novembre", "desembre"
        ];
        return mesos[numero] || "";
    }

    function formatData(dia, mes, any) {
        if (!any || any === 0) return "";

        const parts = [];

        if (dia && dia !== 0) parts.push(dia);
        if (mes && mes !== 0) parts.push(getMesCatalan(parseInt(mes)));
        parts.push(any);

        return parts.join(" ");
    }

    // Función para obtener datos de la API y luego crear la tabla
    function obtenerDatos(url, taula, columna1, urlFitxa, urlFitxa2) {
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta de la API');
                }
                return response.json();
            })
            .then(data => {
                // Llamamos a la función para crear la tabla con los datos obtenidos
                crearTabla(data, taula, columna1, urlFitxa, urlFitxa2);
            })
            .catch(error => {
                console.error('Hubo un problema con la solicitud fetch:', error);
            });
    }

    function crearTabla(datos, taula, columna1, urlFitxa, urlFitxa2) {
        // Obtener el div donde se insertará la tabla
        const taula1 = document.getElementById(taula);

        // Limpiar cualquier contenido previo en taula1 (por si se vuelve a cargar la tabla)
        taula1.innerHTML = '';

        // Comprobar si los datos están vacíos o si hay un mensaje de error
        if (datos.length === 0 || datos.error === "No rows found") {
            // Mostrar mensaje de que no hay datos
            const mensaje = document.createElement('p');
            mensaje.textContent = "No hi ha dades per mostrar";
            taula1.appendChild(mensaje);
            return; // Salir de la función sin crear la tabla
        }

        // Crear la tabla
        const tabla = document.createElement('table');
        tabla.classList.add('table', 'table-striped');

        // Crear el encabezado de la tabla
        const thead = document.createElement('thead');
        thead.classList.add('table-primary');
        const filaEncabezado = document.createElement('tr');
        const columnaNom = document.createElement('th');
        columnaNom.textContent = columna1;
        const columnaAccions = document.createElement('th');
        columnaAccions.textContent = 'Accions';

        filaEncabezado.appendChild(columnaNom);
        filaEncabezado.appendChild(columnaAccions);
        thead.appendChild(filaEncabezado);

        // Crear el cuerpo de la tabla
        const tbody = document.createElement('tbody');

        // Llenar la tabla con los datos recibidos por la API
        datos.forEach(item => {
            const fila = document.createElement('tr');

            // Columna "Nom i cognoms"
            const columnaNomCognoms = document.createElement('td');
            columnaNomCognoms.innerHTML = `<a href="${window.location.origin}/gestio/historia/fitxa-${urlFitxa}/${item.slug}">${item.nom}</a>`;

            // Columna "Accions" (ejemplo con botones de editar y eliminar)
            const columnaAccions = document.createElement('td');
            columnaAccions.innerHTML = `<a href="https://${window.location.host}/gestio/historia/modifica-esdeveniment-${urlFitxa2}/${item.id}">
                <button type="button" class="button btn-petit">Modifica</button>`;

            fila.appendChild(columnaNomCognoms);
            fila.appendChild(columnaAccions);
            tbody.appendChild(fila);
        });

        // Añadir el encabezado y el cuerpo de la tabla a la tabla principal
        tabla.appendChild(thead);
        tabla.appendChild(tbody);

        // Añadir la tabla al div taula1
        taula1.appendChild(tabla);
    }
</script>