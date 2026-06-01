<?php
// Obtener la URL completa
$url2 = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($url2);
$path = $parsedUrl['path'];
$segments = explode("/", trim($path, "/"));

if ($segments[2] === "modifica-vault") {
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
        // Llenar selects con opciones
        selectOmplirDades("/api/vault/get/?tipusServeis", "", "tipus", "tipus");
    </script>
<?php
}
?>

<div class="container-fluid form">
    <?php
    if ($modificaBtn === 1) {
    ?>
        <h2>Modificar Vault</h2>
        <h4 id="bookUpdateTitle"></h4>
    <?php
    } else {
    ?>
        <h2>Creació d'una nova clau</h2>
    <?php
    }
    ?>

    <div class="alert alert-success" id="missatgeOk" style="display:none">
    </div>

    <div class="alert alert-danger" id="missatgeErr" style="display:none">
    </div>

    <form method="POST" action="" id="modificaVault" class="row g-3">
        <?php
        if ($modificaBtn === 1) {
        ?>
            <input type="hidden" id="id" name="id" value="">
        <?php
        }
        ?>

        <div class="col-md-4">
            <label>Servei:</label>
            <input class="form-control" type="text" name="servei" id="servei" value="">
        </div>

        <div class="col-md-4">
            <label>Usuari:</label>
            <input class="form-control" type="text" name="usuari" id="usuari" value="">
        </div>

        <div class="col-md-4">
            <label>Contrasenya:</label>
            <input class="form-control" type="password" name="password" id="password" value="">
        </div>

        <div class="col-md-4">
            <label>Clau 2Factor:</label>
            <input class="form-control" type="password" name="clau2f" id="clau2f" value="">
        </div>

        <div class="col-md-4">
            <label>Pàgina web:</label>
            <input class="form-control" type="text" name="web" id="web" value="">
        </div>

        <div class="col-md-4">
            <label>Tipus de servei:</label>
            <select class="form-select" name="tipus" id="tipus" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Notes:</label>
            <input class="form-control" type="text" name="notes" id="notes" value="">
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
                        <button type="submit" class="btn btn-primary">Modifica llibre</button>
                    <?php
                    } else {
                    ?>
                        <button type="submit" class="btn btn-primary">Crea nou llibre</button>
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
        let urlAjax = "/api/vault/get/?serveiId=" + id;

        fetch(urlAjax, {
                method: "GET",
            })
            .then(response => response.json())
            .then(data => {
                // Establecer valores en los campos del formulario
                const newContent = "Servei: " + data.servei;
                const h2Element = document.getElementById('bookUpdateTitle');
                h2Element.innerHTML = newContent;

                document.getElementById('servei').value = data.servei;
                document.getElementById('usuari').value = data.usuari;
                document.getElementById('web').value = data.web;
                document.getElementById('notes').value = data.notes;
                document.getElementById("id").value = data.id;

                // Llenar selects con opciones
                selectOmplirDades("/api/vault/get/?tipusServeis", data.tipus, "tipus", "tipus");

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