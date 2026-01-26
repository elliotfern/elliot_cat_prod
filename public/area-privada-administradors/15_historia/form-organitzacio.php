<?php
// Obtener la URL completa
$url2 = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($url2);
$path = $parsedUrl['path'];
$segments = explode("/", trim($path, "/"));

if ($segments[2] === "modifica-organitzacio") {
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
        selectOmplirDades("/api/biblioteca/get/?type=ciutat", "", "orgCiutat", "ciutat");
        selectOmplirDades("/api/historia/get/?llistatSubEtapes", "", "orgSubEtapa", "nomSubEtapa");
        selectOmplirDades("/api/historia/get/?llistatImatgesOrganitzacions", "", "img", "nom");
        selectOmplirDades("/api/historia/get/?llistatIdeologies", "", "orgIdeologia", "ideologia");
        selectOmplirDades("/api/biblioteca/get/?type=pais", "", "orgPais", "pais_cat");
        selectOmplirDades("/api/historia/get/?llistatTipusOrganitzacio", "", "orgTipus", "nomTipus");
    </script>
<?php
}
?>

<div class="barraNavegacio">
    <h6><a href="<?php echo APP_INTRANET; ?>">Intranet</a> > <a href="<?php echo APP_INTRANET . $url['historia']; ?>">Base de dades Història</a> > <a href="<?php echo APP_INTRANET . $url['historia']; ?>/llistat-organitzacions">Llistat d'organitzacions</a> </h6>
</div>

<div class="container-fluid form">
    <?php
    if ($modificaBtn === 1) {
    ?>
        <h2>Modificar l'organització</h2>
        <h4 id="nom"></h4>
    <?php
    } else {
    ?>
        <h2>Creació d'un nova organització</h2>
    <?php
    }
    ?>

    <div class="alert alert-success" id="missatgeOk" style="display:none" role="alert">
    </div>

    <div class="alert alert-danger" id="missatgeErr" style="display:none" role="alert">
    </div>

    <form method="POST" action="" id="formOrganitzacio" class="row g-3">
        <?php
        if ($modificaBtn === 1) {
        ?>
            <input type="hidden" id="id" name="id" value="">
        <?php
        }
        ?>

        <div class="col-md-4">
            <label>Organització (català):</label>
            <input class="form-control" type="text" name="nomOrg" id="nomOrg" value="">
        </div>

        <div class="col-md-4">
            <label>Organització (castellà):</label>
            <input class="form-control" type="text" name="nomOrgCast" id="nomOrgCast" value="">
        </div>

        <div class="col-md-4">
            <label>Organització (anglès):</label>
            <input class="form-control" type="text" name="nomOrgEng" id="nomOrgEng" value="">
        </div>

        <div class="col-md-4">
            <label>Organització (italià):</label>
            <input class="form-control" type="text" name="nomOrgIt" id="nomOrgIt" value="">
        </div>

        <div class="col-md-4">
            <label>Slug:</label>
            <input class="form-control" type="text" name="slug" id="slug" value="">
        </div>

        <div class="col-md-4">
            <label>Sigla:</label>
            <input class="form-control" type="text" name="orgSig" id="orgSig" value="">
        </div>

        <div class="col-md-4">
            <label>Any fundació:</label>
            <input class="form-control" type="text" name="dataFunda" id="dataFunda" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Any dissolució:</label>
            <input class="form-control" type="text" name="dataDiss" id="dataDiss" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Sub-etapa històrica:</label>
            <select class="form-select" name="orgSubEtapa" id="orgSubEtapa" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Ciutat:</label>
            <select class="form-select" name="orgCiutat" id="orgCiutat" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>País:</label>
            <select class="form-select" name="orgPais" id="orgPais" value="">
            </select>
        </div>


        <div class="col-md-4">
            <label>Tipus d'organització:</label>
            <select class="form-select" name="orgTipus" id="orgTipus" value="">
            </select>
        </div>


        <div class="col-md-4">
            <label>Ideologia:</label>
            <select class="form-select" name="orgIdeologia" id="orgIdeologia" value="">
            </select>
        </div>


        <div class="col-md-4">
            <label>Imatge:</label>
            <select class="form-select" name="img" id="img" value="">
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
                        <button type="submit" class="btn btn-primary">Modifica organització</button>
                    <?php
                    } else {
                    ?>
                        <button type="submit" class="btn btn-primary">Crea nova organització</button>
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
        let urlAjax = "/api/historia/get/?fitxaOrganitzacio=" + slug;

        fetch(urlAjax, {
                method: "GET",
            })
            .then(response => response.json())
            .then(data => {
                // Establecer valores en los campos del formulario
                const newContent = "Organització: " + data.nomOrg;
                const h2Element = document.getElementById('nom');
                h2Element.innerHTML = newContent;

                document.getElementById('nomOrg').value = data.nomOrg;
                document.getElementById('nomOrgCast').value = data.nomOrgCast;
                document.getElementById('nomOrgEng').value = data.nomOrgEng;
                document.getElementById('nomOrgIt').value = data.nomOrgIt;
                document.getElementById('orgSig').value = data.orgSig;

                document.getElementById('slug').value = data.slug;
                document.getElementById("id").value = data.id;

                document.getElementById("dataFunda").value = data.dataFunda === 0 ? '' : data.dataFunda;
                document.getElementById("dataDiss").value = data.dataDiss === 0 ? '' : data.dataDiss;

                // Llenar selects con opciones
                selectOmplirDades("/api/biblioteca/get/?type=ciutat", data.orgCiutat, "orgCiutat", "ciutat");
                selectOmplirDades("/api/historia/get/?llistatSubEtapes", data.orgSubEtapa, "orgSubEtapa", "nomSubEtapa");
                selectOmplirDades("/api/historia/get/?llistatImatgesOrganitzacions", data.img, "img", "nom");
                selectOmplirDades("/api/historia/get/?llistatIdeologies", data.orgIdeologia, "orgIdeologia", "ideologia");
                selectOmplirDades("/api/biblioteca/get/?type=pais", data.orgPais, "orgPais", "pais_cat");
                selectOmplirDades("/api/historia/get/?llistatTipusOrganitzacio", data.orgTipus, "orgTipus", "nomTipus");

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