<main>
    <div class="container">
        <h1>Lectura de Feeds RSS</h1>

        <!-- Botones de categorías -->
        <div id="botones" class="requadre">
            <button id="btnBlogs" class="btn-gran btn-primari">Blogs</button>
            <button id="btnMedios" class="btn-gran btn-primari">Mitjans de comunicació</button>
        </div>

        <!-- Botones de feeds agrupados por categoría -->
        <div id="grupoBlogs" class="hidden requadre">
            <button id="btnFeed1" class="btn-gran btn-secondari">Jaime Gómez-Obregon</button>
            <button id="btnFeed7" class="btn-gran btn-secondari">The Cheis</button>

        </div>

        <div id="grupoMedios" class="hidden requadre">
            <button id="btnFeed2" class="btn-gran btn-secondari">Vilaweb</button>
            <button id="btnFeed3" class="btn-gran btn-secondari">El Pais</button>
            <button id="btnFeed4" class="btn-gran btn-secondari">La Vanguardia</button>
            <button id="btnFeed5" class="btn-gran btn-secondari">BBC World</button>
            <button id="btnFeed6" class="btn-gran btn-secondari">Ara</button>
        </div>

        <div id="feed"></div>
    </div>
</main>

<style>
    .requadre {
        margin-bottom: 30px;
    }

    #feed {
        margin-top: 20px;
        text-align: left;
        display: inline-block;
        width: 80%;
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }

    .hidden {
        display: none;
    }

    /* Asegura que las imágenes no se desborden del contenedor */
    img {
        max-width: 100%;
        /* La imagen no puede exceder el ancho del contenedor */
        height: auto;
        /* Mantiene la relación de aspecto original de la imagen */
        object-fit: contain;
        /* Ajusta la imagen para que se ajuste dentro del contenedor sin recortar */
    }
</style>