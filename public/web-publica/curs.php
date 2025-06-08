<?php
$slug = $routeParams[0];
?>
<main>
    <div class="container">
        <h2 class="text-center bold" id="course-title"></h2>
        <h5 class="text-center italic" id="course-subtitle"></h5>

        <h1>Articles:</h1>
        <ul id="courseList"></ul>

        <hr />
    </div>
</main>
<script>
    const nameCourse = "<?php echo $slug; ?>";
    const lang = "ca";
    // Función para obtener los cursos desde la API
    async function obtenerCursos(nameCourse, lang) {
        try {
            // Realiza la solicitud fetch a la API
            const response = await fetch(`https://elliot.cat/api/historia/get/?type=curso&paramName=${nameCourse}&langCurso=${lang}`);

            // Verifica si la respuesta es exitosa
            if (!response.ok) {
                throw new Error('Error en la solicitud a la API');
            }

            // Convierte la respuesta a JSON
            const data = await response.json();

            // Muestra los cursos
            mostrarCursos(data);
        } catch (error) {
            console.error('Hubo un problema con la solicitud Fetch:', error);
        }
    }

    // Función para mostrar los cursos en la lista
    function mostrarCursos(cursos) {
        const listaCursos = document.getElementById('courseList');

        // Limpiar la lista antes de agregar los nuevos elementos
        listaCursos.innerHTML = '';

        // Iterar sobre cada curso y agregarlo a la lista
        cursos.forEach(curso => {
            const li = document.createElement('li');
            const postLink = `https://elliot.cat/ca/article/${curso.post_name}`; // Construye el link

            li.innerHTML = `
          <h6><a href="${postLink}">${curso.post_title}</a></h6>

        `;

            listaCursos.appendChild(li);
        });
    }

    obtenerCursos(nameCourse, lang);
</script>