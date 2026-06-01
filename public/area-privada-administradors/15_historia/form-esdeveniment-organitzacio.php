<?php
// Obtener la URL completa
$url2 = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($url2);
$path = $parsedUrl['path'];
$segments = explode("/", trim($path, "/"));

if ($segments[2] === "modifica-esdeveniment-organitzacio") {
    $slug = $routeParams[0];
    if (is_numeric($slug) && ctype_digit($slug)) {
        // Es un número entero (positivo), lo convertimos
        $slug = (int)$slug;

        // Acción para enteros
        $modificaBtn = 2; // form put
        $id = $slug;
?>
        <script type="module">
            formUpdateLlibre2("<?php echo $id; ?>");
        </script>
    <?php
    } else {
        // Es una cadena (texto)
        $modificaBtn = 1; // form post
    ?>
        <script type="module">
            formUpdateLlibre("<?php echo $slug; ?>");
        </script>
<?php
    }
}
?>

<div class="barraNavegacio">
    <h6><a href="<?php echo APP_INTRANET; ?>">Intranet</a> > <a href="<?php echo APP_INTRANET . $url['historia']; ?>">Base de dades Història</a> > <a href="<?php echo APP_INTRANET . $url['historia']; ?>/llistat-esdeveniments">Llistat d'esdeveniments</a> </h6>
</div>

<div class="container-fluid form">
    <?php
    if ($modificaBtn === 1) {
    ?>
        <h2>Vinculació d'un esdeveniment històric amb una organització</h2>
        <h4 id="bookUpdateTitle"></h4>
    <?php
    } else {
    ?>
        <h2>Modificació del vincle entre un esdeveniment històric amb una organització</h2>
    <?php
    }
    ?>

    <div class="alert alert-success" id="missatgeOk" style="display:none" role="alert">
    </div>

    <div class="alert alert-danger" id="missatgeErr" style="display:none" role="alert">
    </div>

    <form method="POST" action="" id="formEsdeveniment" class="row g-3">

        <?php
        if ($modificaBtn === 2) {
        ?>
            <input type="hidden" id="id" name="id" value="">
        <?php
        }
        ?>

        <div class="col-md-4">
            <label>Esdeveniment:</label>
            <select class="form-select" name="idEsde" id="idEsde" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Organització:</label>
            <select class="form-select" name="idOrg" id="idOrg" value="">
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
                        <button type="submit" class="btn btn-primary" id="btnPost" data-method="POST">Crea vinculació</button>
                    <?php
                    } else {
                    ?>
                        <button type="submit" class="btn btn-primary" id="btnPut" data-method="PUT">Modifica</button>
                    <?php
                    }
                    ?>

                </div>
            </div>
        </div>
    </form>

</div>

<script>
    function formUpdateLlibre(slug) {
        let urlAjax = "/api/historia/get/?esdeveniment=" + slug;

        fetch(urlAjax, {
                method: "GET",
            })
            .then(response => response.json())
            .then(data => {
                // Establecer valores en los campos del formulario
                const newContent = "Esdeveniment: " + data.esdeNom;
                const h2Element = document.getElementById('bookUpdateTitle');
                h2Element.innerHTML = newContent;

                // Llenar selects con opciones
                selectOmplirDades("/api/historia/get/?llistatEsdevenimentsSelect", data.id, "idEsde", "esdeNom");
                selectOmplirDades("/api/historia/get/?llistatOrganitzacions", "", "idOrg", "nomOrg");

            })
            .catch(error => console.error("Error al obtener los datos:", error));
    }

    function formUpdateLlibre2(id) {
        let urlAjax = "/api/historia/get/?formEsdevenimentOrganitzacions=" + id;

        fetch(urlAjax, {
                method: "GET",
            })
            .then(response => response.json())
            .then(data => {
                // Establecer valores en los campos del formulario
                document.getElementById("id").value = data.id;

                // Llenar selects con opciones
                selectOmplirDades("/api/historia/get/?llistatEsdevenimentsSelect", data.idEsde, "idEsde", "esdeNom");
                selectOmplirDades("/api/historia/get/?llistatOrganitzacions", data.idOrg, "idOrg", "nomOrg");

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

            // Afegir opció per defecte
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.text = 'Selecciona una opció:';
            defaultOption.disabled = false;
            defaultOption.selected = !selectedValue; // si no hi ha valor seleccionat, aquesta es selecciona
            selectElement.appendChild(defaultOption);

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