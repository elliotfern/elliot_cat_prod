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
    const nameCourse = "<?php echo htmlspecialchars($slug, ENT_QUOTES); ?>";

    function getLangFromPath() {
        const parts = window.location.pathname.split('/').filter(Boolean);
        const first = (parts[0] || '').toLowerCase();
        const allowed = ['ca', 'es', 'en', 'fr', 'it'];
        return allowed.includes(first) ? first : 'ca';
    }

    const lang = getLangFromPath();

    // Función para obtener los cursos desde la API
    async function obtenerCursos(nameCourse, lang) {
        try {
            const url = new URL('https://elliot.cat/api/historia/get/cursHistoria');
            url.searchParams.set('paramName', nameCourse);
            url.searchParams.set('langCurso', lang);

            const response = await fetch(url.toString(), {
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error('Error en la solicitud a la API');
            }

            const data = await response.json();

            // Si tu API devuelve {data: [...]}, usa ese; si devuelve directamente [...], cae al mismo
            const cursos = (data && data.data) ? data.data : data;

            mostrarCursos(cursos, lang);
        } catch (error) {
            console.error('Hubo un problema con la solicitud Fetch:', error);
        }
    }

    // Función para mostrar los cursos en la lista
    function mostrarCursos(cursos, lang) {
        const listaCursos = document.getElementById('courseList');
        if (!listaCursos) return;

        listaCursos.innerHTML = '';

        (cursos || []).forEach(curso => {
            const li = document.createElement('li');

            // usa el idioma actual en el link
            const postLink = `https://elliot.cat/${lang}/historia/article/${encodeURIComponent(curso.slug)}`;

            li.innerHTML = `<h6><a href="${postLink}">${curso.post_title}</a></h6>`;
            listaCursos.appendChild(li);
        });
    }

    obtenerCursos(nameCourse, lang);
</script>