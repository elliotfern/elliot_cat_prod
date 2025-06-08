<?php
$slug = $routeParams[0];

?>
<main>
    <div class="container">
        <h2 class="text-center bold"></h2>
        <h5 class="text-center italic"></h5>

        <!-- Información del autor -->
        <div class="author-box">
            <!-- Contenido del AuthorBox -->
        </div>

        <p>

        </p>

        <!-- Banderas de idiomas -->
        <div class="translate-articles">

        </div>

        <!-- Índice de contenidos -->
        <div class="index-continguts">
        </div>

        <!-- Contenido del artículo -->
        <div id="text-article">

        </div>

        <div id="text-article-error">

        </div>

        <hr />

        <!-- Artículos relacionados -->
        <div>
            <h3>
                Curso relacionado:
                <a href="/es/course/curso-titulo">Título del curso</a>
            </h3>
            <ul>
                <li>
                    <a href="/es/article/related-article-1">Artículo relacionado 1</a>
                </li>
                <li>
                    <a href="/es/article/related-article-2">Artículo relacionado 2</a>
                </li>
            </ul>
        </div>
    </div>
</main>
<script>
    // Función para obtener el artículo
    const articleContainer = document.getElementById('text-article'); // Un div con id="article-container" en el HTML donde se imprimirá el artículo
    const errorContainer = document.getElementById('text-article-error'); // Un div con id="error-container" para mostrar el error si ocurre

    async function getData() {
        // Variables necesarias
        const nameArticle = "<?php echo $slug; ?>"; // Sustituir por el nombre del artículo que deseas

        try {
            // Realiza la solicitud a la API
            const response = await fetch(`https://elliot.cat/api/historia/get/?type=articleName&paramName=${nameArticle}`);
            const data = await response.json();

            // Verifica si los datos son correctos y contiene el contenido del artículo
            if (data && data.post_content) {
                // Si tiene contenido, lo inserta en el contenedor del artículo
                articleContainer.innerHTML = `
        <h2 class="text-center bold">${data.post_title}</h2>
        <h5 class="text-center italic">${data.post_excerpt}</h5>
        <div class="author-box">
          <!-- Información del autor -->
        </div>
        <p>Fecha de publicación: ${formatFecha(data.post_date)} | Última modificación: ${formatFecha(data.post_modified)}</p>
        <div class="text-article">${data.post_content}</div>
      `;
                errorContainer.style.display = 'none'; // Ocultar el contenedor de error si todo va bien
            } else {
                throw new Error('El artículo no tiene contenido');
            }
        } catch (error) {
            // Si hay un error, lo muestra en el contenedor de error
            console.error('Error al obtener el artículo:', error);
            errorContainer.innerHTML = '<p>Hubo un error al cargar el artículo. Intenta más tarde.</p>';
            errorContainer.style.display = 'block'; // Mostrar el contenedor de error
        }
    }

    // Función para formatear las fechas (puedes ajustarla a tus necesidades)
    function formatFecha(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString(); // Puedes personalizar el formato de fecha aquí
    }

    // Llamar a la función cuando la página cargue o según sea necesario
    getData();
</script>