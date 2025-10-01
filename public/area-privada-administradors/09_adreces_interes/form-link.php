<?php
// Obtener la URL completa
$url2 = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($url2);
$path = $parsedUrl['path'];
$segments = explode("/", trim($path, "/"));

if ($segments[2] === "modifica-link") {
    $modificaBtn = 1;
    $id = $routeParams[0];
} else {
    $modificaBtn = 2;
}

if ($modificaBtn === 1) {
?>
    <script type="module">
        formUpdateLlibre("<?php echo $id; ?>");
    </script>
<?php
} else {
?>
    <script type="module">
        //     cat lang tipus
        selectOmplirDades("/api/adreces/get/?type=totsTemes", "", "cat", "tema_categoria");
        selectOmplirDades("/api/biblioteca/get/?type=llengues", "", "lang", "idioma_ca");
        selectOmplirDades("/api/adreces/get/?type=all-types", "", "tipus", "type_ca");
    </script>
<?php
}
?>

<div class="barraNavegacio">
        <h6><a href="<?php echo APP_INTRANET; ?>">Intranet</a> > <a href="<?php echo APP_INTRANET . $url['adreces']; ?>">Adreces d'interès</a> > <a href="<?php echo APP_INTRANET . $url['adreces']; ?>/nou-link">Inserció enllaç</a></h6>
    </div>

<div class="container-fluid form">
    <?php
    if ($modificaBtn === 1) {
    ?>
        <h2>Modificar les dades de l'enllaç</h2>
        <h4 id="bookUpdateTitle"></h4>
    <?php
    } else {
    ?>
        <h2>Creació d'un nou enllaç</h2>
    <?php
    }
    ?>

    <div class="alert alert-success" id="missatgeOk" style="display:none" role="alert">
        <h4 class="alert-heading"><strong></strong></h4>
        <h6></h6>
    </div>

    <div class="alert alert-danger" id="missatgeErr" style="display:none" role="alert">
        <h4 class="alert-heading"><strong></strong></h4>
        <h6></h6>
    </div>

    <form method="POST" action="" id="modificalink" class="row g-3">
        <?php
        if ($modificaBtn === 1) {
        ?>
            <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
        <?php
        }
        ?>

        <div class="col-md-4">
            <label>Nom enllaç:</label>
            <input class="form-control" type="text" name="nom" id="nom" value="">
        </div>

        <div class="col-md-4">
            <label>Pàgina web:</label>
            <input class="form-control" type="text" name="web" id="web" value="">
        </div>

        <div class="col-md-4">
            <label>Categoria enllaç:</label>
            <select class="form-select" name="cat" id="cat" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Idioma:</label>
            <select class="form-select" name="lang" id="lang" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Tipus enllaç:</label>
            <select class="form-select" name="tipus" id="tipus" value="">
            </select>
        </div>

        <div class="col-md-4">
        </div>

        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left">
                    <a href="#" onclick="window.history.back()" class="btn btn-secondary">Tornar enrere</a>
                </div>
                <div class="col-6 text-right derecha">
                    <?php
                    if ($modificaBtn === 1) {
                    ?>
                        <button type="submit" class="btn btn-primary">Modifica enllaç</button>
                    <?php
                    } else {
                    ?>
                        <button type="submit" class="btn btn-primary">Crea nou enllaç</button>
                    <?php
                    }
                    ?>

                </div>
            </div>
        </div>
    </form>

</div>

<script>
    function formUpdateLlibre(id) {
        let urlAjax = "/api/adreces/get/?linkId=" + id;

        fetch(urlAjax, {
                method: "GET",
            })
            .then(response => response.json())
            .then(data => {
                // Establecer valores en los campos del formulario
                const newContent = "Enllaç: " + data.nom;
                const h2Element = document.getElementById('bookUpdateTitle');
                h2Element.innerHTML = newContent;

                document.getElementById('nom').value = data.nom;
                document.getElementById('web').value = data.web;
                document.getElementById("id").value = data.id;

                // Llenar selects con opciones
                selectOmplirDades("/api/adreces/get/?type=totsTemes", data.cat, "cat", "tema_categoria");
                selectOmplirDades("/api/biblioteca/get/?type=llengues", data.lang, "lang", "idioma_ca");
                selectOmplirDades("/api/adreces/get/?type=all-types", data.tipus, "tipus", "type_ca");


            })
            .catch(error => console.error("Error al obtener los datos:", error));
    }

    async function selectOmplirDades(url, selectedValue, selectId, textField) {
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error('Error en la sol·licitud AJAX');
            }

            const data = await response.json();
            const selectElement = document.getElementById(selectId);
            if (!selectElement) {
                console.error(`Select element with id ${selectId} not found`);
                return;
            }

            // Netejar les opcions actuals
            selectElement.innerHTML = '';

            // Afegir les noves opcions
            data.forEach((item) => {
                const option = document.createElement('option');
                option.value = item.id;
                option.text = item[textField];
                if (item.id === selectedValue) {
                    option.selected = true;
                }
                selectElement.appendChild(option);
            });
        } catch (error) {
            console.error('Error:', error);
        }
    }
</script>

<style>
    /* Establecer un contenedor flex para la fila */
    .form .row {
        display: flex;
        flex-wrap: wrap;
        /* Permite que los elementos se muevan a la siguiente fila cuando no haya espacio */
        gap: 15px;
        /* Añade un espacio entre las columnas */
    }

    /* Hacer que cada columna ocupe el 50% del ancho */
    .form .col-md-4 {
        flex: 0 0 30%;
        /* 48% de ancho para que haya espacio entre las columnas */
        box-sizing: border-box;
        /* Asegura que el padding no afecte el tamaño total */
    }

    .form .col-complet {
        flex: 0 0 100%;
        /* 48% de ancho para que haya espacio entre las columnas */
        box-sizing: border-box;
        /* Asegura que el padding no afecte el tamaño total */
    }

    /* Asegurarse de que las columnas se ajusten bien en pantallas pequeñas */
    @media (max-width: 768px) {
        .form .col-md-4 {
            flex: 0 0 100%;
            /* En pantallas más pequeñas, cada columna ocupa el 100% del ancho */
        }
    }

    /* Asegúrate de que el contenedor tenga un display flex para la fila */
    .container .row {
        display: flex;
        justify-content: space-between;
        /* Distribuye los botones con espacio entre ellos */
        padding: 10px 0;
    }

    .form {
        margin-bottom: 100px;
    }

    .container {
        padding-bottom: 10px !important;
    }

    /* Estilos opcionales para los botones */
    .btn {
        padding: 10px 20px;
        /* Espaciado interno para los botones */
        font-size: 16px;
        /* Tamaño de la fuente */
        text-align: center;
        /* Asegura que el texto esté centrado */
        cursor: pointer;
        /* Cambia el cursor cuando pasa sobre el botón */
    }

    .btn-secondary {
        background-color: #6c757d;
        /* Color de fondo para el botón secundario */
        border: none;
        /* Eliminar borde */
        color: white;
        /* Color del texto */
    }

    .btn-primary {
        background-color: #007bff;
        /* Color de fondo para el botón primario */
        border: none;
        /* Eliminar borde */
        color: white;
        /* Color del texto */
    }

    /* Ajuste para móviles (si lo deseas) */
    @media (max-width: 768px) {
        .container .row {
            flex-direction: column;
            /* Hace que los botones se apilen verticalmente en pantallas pequeñas */
            align-items: center;
            /* Centra los botones */
        }

        .container .row .col-6 {
            width: 100%;
            /* Hace que las columnas ocupen el 100% del ancho en pantallas pequeñas */
            text-align: center;
            /* Centra el texto en pantallas pequeñas */
        }
    }
</style>