<main>
    <div class="container">
        <!-- Título principal -->
        <h4 class="text-center mt-4">Título de la Web</h4>

        <!-- Caja del autor -->
        <div class="author-box text-center">
            <!-- Sustituir esto por el contenido del AuthorBox -->
            <p>Autor: Elliot Fernandez</p>
        </div>

        <!-- Badges de lenguajes -->
        <div class="text-center mt-4">
            <img
                src="https://img.shields.io/badge/JavaScript-%23F7DF1E?style=for-the-badge&logo=javascript&logoColor=white"
                alt="JavaScript" />
            <img
                src="https://img.shields.io/badge/TypeScript-%23007ACC?style=for-the-badge&logo=typescript&logoColor=white"
                alt="TypeScript" />
            <img
                src="https://img.shields.io/badge/Java-%23007396?style=for-the-badge&logo=java&logoColor=white"
                alt="Java" />
            <img
                src="https://img.shields.io/badge/HTML5-%23E34F26?style=for-the-badge&logo=html5&logoColor=white"
                alt="HTML5" />
            <img
                src="https://img.shields.io/badge/CSS3-%231572B6?style=for-the-badge&logo=css3&logoColor=white"
                alt="CSS3" />
            <img
                src="https://img.shields.io/badge/PHP-%23777BB4?style=for-the-badge&logo=php&logoColor=white"
                alt="PHP" />
            <img
                src="https://img.shields.io/badge/React-%2361DAFB?style=for-the-badge&logo=react&logoColor=white"
                alt="React" />
            <img
                src="https://img.shields.io/badge/Node.js-%23339933?style=for-the-badge&logo=node.js&logoColor=white"
                alt="Node.js" />
            <img
                src="https://img.shields.io/badge/MongoDB-%2300A92D?style=for-the-badge&logo=mongodb&logoColor=white"
                alt="MongoDB" />
            <img
                src="https://img.shields.io/badge/MySQL-%234479A1?style=for-the-badge&logo=mysql&logoColor=white"
                alt="MySQL" />
        </div>

        <!-- Sección Historia Oberta -->
        <h4 class="text-center mt-4">Título Historia Oberta</h4>
        <h5 class="text-center mb-4">Descripción de Historia Oberta</h5>

        <style>
            /* Estilos generales */
            .gridContainer {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                /* 3 columnas por fila en pantallas grandes */
                gap: 20px;
                padding: 20px;
            }

            .gridItem {
                border: 1px solid #ccc;
                padding: 10px;
                text-align: center;
            }

            .gridItem img {
                width: 100%;
                height: auto;
                border-bottom: 1px solid #ccc;
            }

            /* Estilo para pantallas más pequeñas */
            @media (max-width: 1024px) {
                .gridContainer {
                    grid-template-columns: repeat(2, 1fr);
                    /* 2 columnas por fila en pantallas medianas */
                }
            }

            @media (max-width: 600px) {
                .gridContainer {
                    grid-template-columns: 1fr;
                    /* 1 columna por fila en pantallas pequeñas */
                }
            }
        </style>

        <div id="coursesList" class="gridContainer"></div>

        <script>
            function getLangFromPath() {
                const parts = window.location.pathname.split('/').filter(Boolean);
                const first = String(parts[0] || '').toLowerCase();
                const allowed = ['ca', 'es', 'en', 'fr', 'it', 'pt'];
                return allowed.includes(first) ? first : 'ca';
            }

            const currentLang = getLangFromPath();

            // Función para obtener y mostrar la lista de cursos
            const getCoursesList = async (lang) => {
                try {
                    const url = new URL('https://elliot.cat/api/historia/get/');
                    url.searchParams.set('type', 'llistatCursos');
                    url.searchParams.set('langCurso', lang);

                    const response = await fetch(url.toString(), {
                        credentials: 'include'
                    });
                    if (!response.ok) throw new Error(`HTTP_${response.status}`);

                    const data = await response.json();

                    // si tu API devuelve {data:[...]}, esto lo soporta; si devuelve [...], también
                    const courses = (data && data.data) ? data.data : data;

                    displayCourses(courses, lang);
                } catch (error) {
                    console.error('Error fetching data:', error);
                }
            };

            // Función para mostrar los cursos
            const displayCourses = (courses, lang) => {
                const coursesListContainer = document.getElementById('coursesList');
                if (!coursesListContainer) return;

                coursesListContainer.innerHTML = '';

                (courses || []).forEach((course) => {
                    const courseElement = document.createElement('div');
                    courseElement.classList.add('gridItem');

                    // ✅ ahora el link usa el idioma actual
                    const courseLink = `/${lang}/historia/curs/${encodeURIComponent(course.paramName)}`;

                    courseElement.innerHTML = `
        <a href="${courseLink}">
          <img src="${course.img}" alt="${course.nombreCurso || ''}" />
        </a>
        <a href="${courseLink}" style="text-decoration: none; color: inherit;">
          <h3 style="font-size: 17px; color: inherit;">${course.nombreCurso || ''}</h3>
        </a>
        <p>${course.resumen || ''}</p>
        <p>
          <a href="${courseLink}">Enlace al curso</a>
        </p>
      `;

                    coursesListContainer.appendChild(courseElement);
                });
            };

            // ✅ Cargar cursos con el idioma de la URL actual
            getCoursesList(currentLang);
        </script>

    </div>
</main>