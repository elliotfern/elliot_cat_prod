<?php
// Obtener la URL completa
$url2 = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($url2);
$path = $parsedUrl['path'];
$segments = explode("/", trim($path, "/"));

if ($segments[2] === "modifica-espai") {
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
        selectOmplirDades("/api/biblioteca/get/?type=ciutat", "", "idCiutat", "ciutat");
        selectOmplirDades("/api/viatges/get/?llistatImatgesEspais", "", "img", "nom");
        selectOmplirDades("/api/viatges/get/?llistatTipusEspais", "", "EspTipus", "TipusNom");
    </script>
<?php
}
?>

<div class="barraNavegacio">
    <h6><a href="<?php echo APP_INTRANET; ?>">Intranet</a> > <a href="<?php echo APP_INTRANET . $url['viatges']; ?>">Viatges</a> > <a href="<?php echo APP_INTRANET . $url['viatges']; ?>/llistat-espais">Llistat d'espais</a> </h6>
</div>

<div class="container-fluid form">
    <?php
    if ($modificaBtn === 1) {
    ?>
        <h2>Modificar l'espai</h2>
        <h4 id="nomEspai"></h4>
    <?php
    } else {
    ?>
        <h2>Creació d'un nou espai</h2>
    <?php
    }
    ?>

    <div class="alert alert-success" id="missatgeOk" style="display:none" role="alert">
    </div>

    <div class="alert alert-danger" id="missatgeErr" style="display:none" role="alert">
    </div>

    <form method="POST" action="" id="formEspai" class="row g-3">
        <?php
        if ($modificaBtn === 1) {
        ?>
            <input type="hidden" id="id" name="id" value="">
        <?php
        }
        ?>

        <div class="col-md-4">
            <label>Nom espai (català):</label>
            <input class="form-control" type="text" name="nom" id="nom" value="">
        </div>

        <div class="col-md-4">
            <label>Nom espai (castellà):</label>
            <input class="form-control" type="text" name="EspNomCast" id="EspNomCast" value="">
        </div>

        <div class="col-md-4">
            <label>Nom espai (anglès):</label>
            <input class="form-control" type="text" name="EspNomEng" id="EspNomEng" value="">
        </div>

        <div class="col-md-4">
            <label>Nom espai (italià):</label>
            <input class="form-control" type="text" name="EspNomIt" id="EspNomIt" value="">
        </div>

        <div class="col-md-4">
            <label>Slug:</label>
            <input class="form-control" type="text" name="slug" id="slug" value="">
        </div>

        <div class="col-md-4">
            <label>Any fundació:</label>
            <input class="form-control" type="text" name="EspFundacio" id="EspFundacio" value="">
        </div>

        <div class="col-md-4">
            <label>Web:</label>
            <input class="form-control" type="text" name="EspWeb" id="EspWeb" value="">
        </div>

        <div class="col-md-4">
            <label>Coordinades: latitud:</label>
            <input class="form-control" type="text" name="coordinades_latitud" id="coordinades_latitud" value="">
        </div>

        <div class="col-md-4">
            <label>Coordinades: longitud:</label>
            <input class="form-control" type="text" name="coordinades_longitud" id="coordinades_longitud" value="">
        </div>

        <div class="col-md-4">
            <label>Tipus d'espai:</label>
            <select class="form-select" name="EspTipus" id="EspTipus" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Ciutat:</label>
            <select class="form-select" name="idCiutat" id="idCiutat" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Imatge:</label>
            <select class="form-select" name="img" id="img" value="">
            </select>
        </div>

        <div class="col-md-12">
            <label>Descripció (català):</label>
            <textarea id="EspDescripcio" name="EspDescripcio" rows="6" cols="50" value=""> </textarea>
        </div>

        <div class="col-md-12">
            <label>Descripció (castellà):</label>
            <textarea id="EspDescripcioCast" name="EspDescripcioCast" rows="6" cols="50" value=""> </textarea>
        </div>

        <div class="col-md-12">
            <label>Descripció (anglès):</label>
            <textarea id="EspDescripcioEng" name="EspDescripcioEng" rows="6" cols="50" value=""> </textarea>
        </div>

        <div class="col-md-12">
            <label>Descripció (italià):</label>
            <textarea id="EspDescripcioIt" name="EspDescripcioIt" rows="6" cols="50" value=""> </textarea>
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
        let urlAjax = "/api/viatges/get/?fitxaEspai=" + slug;

        fetch(urlAjax, {
                method: "GET",
            })
            .then(response => response.json())
            .then(data => {
                // Establecer valores en los campos del formulario
                const newContent = "Espai: " + data.nom;
                const h2Element = document.getElementById('nomEspai');
                h2Element.innerHTML = newContent;

                document.getElementById("id").value = data.id;
                document.getElementById('nom').value = data.nom;
                document.getElementById('EspNomCast').value = data.EspNomCast;
                document.getElementById('EspNomEng').value = data.EspNomEng;
                document.getElementById('EspNomIt').value = data.EspNomIt;
                document.getElementById('slug').value = data.slug;
                document.getElementById('EspWeb').value = data.EspWeb;
                document.getElementById('EspDescripcio').value = data.EspDescripcio;
                document.getElementById('EspDescripcioCast').value = data.EspDescripcioCast;
                document.getElementById('EspDescripcioEng').value = data.EspDescripcioEng;
                document.getElementById('EspDescripcioIt').value = data.EspDescripcioIt;
                document.getElementById('coordinades_latitud').value = data.coordinades_latitud;
                document.getElementById('coordinades_longitud').value = data.coordinades_longitud;

                document.getElementById("EspFundacio").value = data.EspFundacio === 0 ? '' : data.EspFundacio;

                // Llenar selects con opciones
                selectOmplirDades("/api/biblioteca/get/?type=ciutat", data.idCiutat, "idCiutat", "ciutat");
                selectOmplirDades("/api/viatges/get/?llistatImatgesEspais", data.idImg, "img", "nom");
                selectOmplirDades("/api/viatges/get/?llistatTipusEspais", data.EspTipus, "EspTipus", "TipusNom");

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