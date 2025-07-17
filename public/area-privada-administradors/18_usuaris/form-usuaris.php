<?php
// Obtener la URL completa
$url2 = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($url2);
$path = $parsedUrl['path'];
$segments = explode("/", trim($path, "/"));

if ($segments[2] === "modifica-usuari") {
    $modificaBtn = 1;
    $id = $routeParams[0];
} else {
    $modificaBtn = 2;
}

if ($modificaBtn === 1) {
?>
    <script type="module">
        console.log("ID del usuario: <?php echo $id; ?>");
        formUpdateLlibre("<?php echo $id; ?>");
    </script>
<?php
} else {
?>
    <script type="module">
        // Llenar selects con opciones
        selectOmplirDades("/api/biblioteca/get/?type=ciutat", "", "idCiutat", "city");
        selectOmplirDades("/api/viatges/get/?llistatImatgesEspais", "", "img", "nom");
        selectOmplirDades("/api/viatges/get/?llistatTipusEspais", "", "EspTipus", "TipusNom");
    </script>
<?php
}
?>

<div class="container-fluid form">
    <?php
    if ($modificaBtn === 1) {
    ?>
        <h2>Modificar usuari</h2>
        <h4 id="nomEspai"></h4>
    <?php
    } else {
    ?>
        <h2>Alta nou usuari</h2>
    <?php
    }
    ?>

    <div class="alert alert-success" id="missatgeOk" style="display:none" role="alert">
    </div>

    <div class="alert alert-danger" id="missatgeErr" style="display:none" role="alert">
    </div>

    <form method="POST" action="" id="formUsuari" class="row g-3">
        <?php
        if ($modificaBtn === 1) {
        ?>
            <input type="hidden" data-type="number" id="id" name="id" value="">
        <?php
        }
        ?>

        <input type="hidden" data-type="number" id="avatar" name="avatar" value="1">

        <div class="col-md-4">
            <label>Email</label>
            <input class="form-control" type="email" name="email" id="email" value="">
        </div>

        <div class="col-md-4">
            <label>Password (deixar en blanc):</label>
            <input class="form-control" type="password" name="password" id="password" value="">
        </div>

        <div class="col-md-4">
        </div>

        <div class="col-md-4">
            <label>Nom:</label>
            <input class="form-control" type="text" name="firstName" id="firstName" value="">
        </div>

        <div class="col-md-4">
            <label>Cognom:</label>
            <input class="form-control" type="text" name="lastName" id="lastName" value="">
        </div>

        <div class="col-md-4">
            <label for="userType">Tipus d'usuari:</label>
            <select id="userType" data-type="number" name="userType" class="form-control">
                <option value="1">Administrador</option>
                <option value="2">Usuari</option>
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
                        <button type="submit" class="btn btn-primary">Modifica usuari</button>
                    <?php
                    } else {
                    ?>
                        <button type="submit" class="btn btn-primary">Alta usuari</button>
                    <?php
                    }
                    ?>

                </div>
            </div>
        </div>
    </form>

</div>

<script>
    async function formUpdateLlibre(id) {
        const urlAjax = "https://api.elliot.cat/api/users/" + id;

        try {
            const response = await fetch(urlAjax, {
                method: "GET",
            });

            if (!response.ok) {
                throw new Error(`Error: ${response.statusText}`);
            }

            const data = await response.json();

            const newContent = `Usuari: ${data.nom} ${data.cognom}`;
            const h2Element = document.getElementById('nomEspai');
            h2Element.innerHTML = newContent;

            document.getElementById("id").value = data.id;
            document.getElementById('nom').value = data.nom;
            document.getElementById('email').value = data.email;
            document.getElementById('cognom').value = data.cognom;

            // Asignar el valor al campo select según el tipo de usuario
            const userTypeSelect = document.getElementById('userType');
            if (userTypeSelect) {
                userTypeSelect.value = data.userType; // Esto selecciona el valor adecuado (1 o 2)
            }

        } catch (error) {
            console.error("Error al obtener los datos:", error);
        }
    }
</script>