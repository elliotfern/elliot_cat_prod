<?php
// Obtener la URL completa
$url2 = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($url2);
$path = $parsedUrl['path'];
$segments = explode("/", trim($path, "/"));

if ($segments[2] === "modifica-esdeveniment") {
    $modificaBtn = 1;
    $slug = $routeParams[0];
} else {
    $modificaBtn = 2;
}

if ($modificaBtn === 1) {
?>
    <script type="module">
        formUpdateLlibre("<?php echo $slug; ?>");
    </script>
<?php
} else {
?>
    <script type="module">
        // Llenar selects con opciones
        selectOmplirDades("/api/auxiliars/get/ciutats", "", "esdeCiutat", "ciutat");
        selectOmplirDades("/api/historia/get/llistatSubEtapes", "", "esSubEtapa", "nomSubEtapa");
        selectOmplirDades("/api/auxiliars/get/calendariDies", "", "esdeDataIDia", "dia");
        selectOmplirDades("/api/auxiliars/get/calendariDies", "", "esdeDataFDia", "dia");
        selectOmplirDades("/api/auxiliars/get/calendariMesos", "", "esdeDataIMes", "mes");
        selectOmplirDades("/api/auxiliars/get/calendariMesos", "", "esdeDataFMes", "mes");
        selectOmplirDades("/api/historia/get/llistatImatgesEsdeveniments", "", "img", "alt");
    </script>
<?php
}
?>

<div class="barraNavegacio">
    <h6><a href="<?php echo APP_INTRANET; ?>">Intranet</a> > <a href="<?php echo APP_INTRANET . $url['historia']; ?>">Base de dades Història</a> > <a href="<?php echo APP_INTRANET . $url['historia']; ?>/llistat-esdeveniments">Llistat d'esdeveniments</a> </h6>
</div>

<div class="container-fluid form">
    <?php
    if ($modificaBtn === 1) {
    ?>
        <h2>Modificar un esdeveniment històric</h2>
        <h4 id="bookUpdateTitle"></h4>
    <?php
    } else {
    ?>
        <h2>Creació d'un nou esdeveniment històric </h2>
    <?php
    }
    ?>

    <div class="alert alert-success" id="missatgeOk" style="display:none" role="alert">
    </div>

    <div class="alert alert-danger" id="missatgeErr" style="display:none" role="alert">
    </div>

    <form method="POST" action="" id="formEsdeveniment" class="row g-3">
        <?php $timestamp = date('Y-m-d'); ?>
        <?php
        if ($modificaBtn === 1) {
        ?>
            <input type="hidden" id="id" name="id" value="">
        <?php
        }
        ?>

        <div class="col-md-4">
            <label>Esdeveniment (català):</label>
            <input class="form-control" type="text" name="esdeNom" id="esdeNom" value="">
        </div>


        <div class="col-md-4">
            <label>Slug:</label>
            <input class="form-control" type="text" name="slug" id="slug" value="">
        </div>

        <div class="col-md-4">
        </div>

        <div class="col-md-4">
            <label>Dia inici:</label>
            <select class="form-select" name="esdeDataIDia" id="esdeDataIDia" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Mes inici:</label>
            <select class="form-select" name="esdeDataIMes" id="esdeDataIMes" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Any inici:</label>
            <input class="form-control" type="text" name="esdeDataIAny" id="esdeDataIAny" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Dia fi:</label>
            <select class="form-select" name="esdeDataFDia" id="esdeDataFDia" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Mes fi:</label>
            <select class="form-select" name="esdeDataFMes" id="esdeDataFMes" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Any fi:</label>
            <input class="form-control" type="text" name="esdeDataFAny" id="esdeDataFAny" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Sub-etapa històrica:</label>
            <select class="form-select" name="esSubEtapa" id="esSubEtapa" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Ciutat:</label>
            <select class="form-select" name="esdeCiutat" id="esdeCiutat" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Imatge:</label>
            <select class="form-select" name="img" id="img" value="">
            </select>
        </div>

        <div class="col-md-12">
            <label>Descripció dels fets:</label>
            <textarea id="descripcio" name="descripcio" rows="6" cols="50" value=""> </textarea>
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
                        <button type="submit" class="btn btn-primary">Modifica esdeveniment</button>
                    <?php
                    } else {
                    ?>
                        <button type="submit" class="btn btn-primary">Crea nou esdeveniment</button>
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
        let urlAjax = "/api/historia/get/esdeveniment?slug=" + slug;

        fetch(urlAjax)
            .then(response => response.json())
            .then(data => {

                document.getElementById('bookUpdateTitle').innerHTML =
                    "Esdeveniment: " + data.esdeNom;

                document.getElementById('esdeNom').value = data.esdeNom;
                document.getElementById('descripcio').value = data.descripcio;
                document.getElementById('slug').value = data.slug;
                document.getElementById("id").value = data.id;

                document.getElementById("esdeDataIAny").value =
                    data.esdeDataIAny === 0 ? '' : data.esdeDataIAny;

                document.getElementById("esdeDataFAny").value =
                    data.esdeDataFAny === 0 ? '' : data.esdeDataFAny;

                // selects corregidos
                selectOmplirDades("/api/auxiliars/get/ciutats", data.esdeCiutat, "esdeCiutat", "ciutat");
                selectOmplirDades("/api/historia/get/llistatSubEtapes", data.esSubEtapa, "esSubEtapa", "nomSubEtapa");
                selectOmplirDades("/api/auxiliars/get/calendariDies", data.esdeDataIDia, "esdeDataIDia", "dia");
                selectOmplirDades("/api/auxiliars/get/calendariDies", data.esdeDataFDia, "esdeDataFDia", "dia");
                selectOmplirDades("/api/auxiliars/get/calendariMesos", data.esdeDataIMes, "esdeDataIMes", "mes");
                selectOmplirDades("/api/auxiliars/get/calendariMesos", data.esdeDataFMes, "esdeDataFMes", "mes");
                selectOmplirDades("/api/historia/get/llistatImatgesEsdeveniments", data.img, "img", "alt");

            })
            .catch(error => console.error("Error:", error));
    }

    async function selectOmplirDades(url, selectedValue, selectId, textField) {
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error('Error en la sol·licitud AJAX');
            }

            const json = await response.json();
            const data = json.data || [];

            const selectElement = document.getElementById(selectId);
            if (!selectElement) return;

            selectElement.innerHTML = '';

            // opción por defecto
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.text = 'Selecciona una opció:';
            defaultOption.selected = !selectedValue;
            selectElement.appendChild(defaultOption);

            data.forEach((item) => {
                const option = document.createElement('option');

                option.value = item.id;

                // fallback seguro por si cambia el campo
                option.text =
                    item[textField] ??
                    item.nom ??
                    item.dia ??
                    item.mes ??
                    item.alt ??
                    '';

                if (String(item.id) === String(selectedValue)) {
                    option.selected = true;
                }

                selectElement.appendChild(option);
            });

        } catch (error) {
            console.error('Error:', error);
        }
    }
</script>