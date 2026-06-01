<?php
// Obtener la URL completa
$url2 = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($url2);
$path = $parsedUrl['path'];
$segments = explode("/", trim($path, "/"));

if ($segments[2] === "modifica-persona-carrec") {
    $modificaBtn = 1;
    $id = $routeParams[0];
} else {
    $modificaBtn = 2;
    $slug = $routeParams[0];
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
        formUpdateLlibre2("<?php echo $slug; ?>");
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
        <h2>Modificació d'un càrrec a persona</h2>
        <h4 id="titol"></h4>
    <?php
    } else {
    ?>
        <h2>Creació d'un nou càrrec a persona</h2>
        <h4 id="titol"></h4>
    <?php
    }
    ?>

    <div class="alert alert-success" id="missatgeOk" style="display:none" role="alert">
    </div>

    <div class="alert alert-danger" id="missatgeErr" style="display:none" role="alert">
    </div>

    <form method="POST" action="" id="formPersonaCarrec" class="row g-3">
        <?php
        if ($modificaBtn === 1) {
        ?>
            <input type="hidden" id="id" name="id" value="">
        <?php
        }
        ?>

        <div class="col-md-4">
            <label>Persona:</label>
            <select class="form-select" name="idPersona" id="idPersona" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Càrrec (català):</label>
            <input class="form-control" type="text" name="carrecNom" id="carrecNom" value="">
        </div>

        <div class="col-md-4">
            <label>Càrrec (castellà):</label>
            <input class="form-control" type="text" name="carrecNomCast" id="carrecNomCast" value="">
        </div>

        <div class="col-md-4">
            <label>Càrrec (anglès):</label>
            <input class="form-control" type="text" name="carrecNomEng" id="carrecNomEng" value="">
        </div>

        <div class="col-md-4">
            <label>Càrrec (italià):</label>
            <input class="form-control" type="text" name="carrecNomIt" id="carrecNomIt" value="">
        </div>


        <div class="col-md-4">
        </div>

        <div class="col-md-4">
            <label>Any inici:</label>
            <input class="form-control" type="text" name="carrecInici" id="carrecInici" value="">
        </div>

        <div class="col-md-4">
            <label>Any fi:</label>
            <input class="form-control" type="text" name="carrecFi" id="carrecFi" value="">
        </div>


        <div class="col-md-4">
        </div>

        <div class="col-md-4">
            <label>Organització vinculada:</label>
            <select class="form-select" name="idOrg" id="idOrg" value="">
            </select>
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
    function formUpdateLlibre(id) {
        let urlAjax = "/api/historia/get/?personaCarrec=" + id;

        fetch(urlAjax, {
                method: "GET",
            })
            .then(response => response.json())
            .then(data => {
                // Establecer valores en los campos del formulario
                document.getElementById('titol').innerHTML = `Persona: ${data.nom} ${data.cognoms}`;

                document.getElementById('carrecNom').value = data.carrecNom;
                document.getElementById('carrecNomCast').value = data.carrecNomCast;
                document.getElementById('carrecNomEng').value = data.carrecNomEng;
                document.getElementById('carrecNomIt').value = data.carrecNomIt;
                document.getElementById('carrecInici').value = data.carrecInici;
                document.getElementById('carrecFi').value = data.carrecFi;
                document.getElementById('id').value = data.id;

                // Llenar selects con opciones
                selectOmplirDades("/api/historia/get/?llistatPersones", data.idPersona, "idPersona", "nom");
                selectOmplirDades("/api/historia/get/?llistatOrganitzacions", data.idOrg, "idOrg", "nomOrg");

            })
            .catch(error => console.error("Error al obtener los datos:", error));
    }

    function formUpdateLlibre2(slug) {
        let urlAjax = "/api/persones/get/?persona=" + slug;

        fetch(urlAjax, {
                method: "GET",
            })
            .then(response => response.json())
            .then(data => {
                // Establecer valores en los campos del formulario
                document.getElementById('titol').innerHTML = `Persona: ${data.nom} ${data.cognoms}`;

                // Llenar selects con opciones
                selectOmplirDades("/api/historia/get/?llistatPersones", data.id, "idPersona", "nom");
                selectOmplirDades("/api/historia/get/?llistatOrganitzacions", "", "idOrg", "nomOrg");

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