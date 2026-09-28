<div id="barraNavegacioContenidor"></div>
<h1>Lectura de Feeds RSS</h1>

<div id="lectorRSS"></div>


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

    .requadre button {
        margin: 0 6px 6px 0;
    }

    .requadre button.actiu {
        font-weight: bold;
        text-decoration: underline;
    }
</style>