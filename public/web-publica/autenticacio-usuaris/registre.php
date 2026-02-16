<div class="pantallaLogin">
    <div class="card" style="max-width: 400px;">
        <div class="card-body">
            <div class="container">
                <h3>Registre nou usuari</h3>
                <div class="alert alert-success" id="missatgeOk" style="display:none" role="alert">
                    <strong>Alta correcte!</strong>
                </div>

                <div class="alert alert-danger" id="missatgeErr" style="display:none" role="alert">
                    <strong>Error en les dades</strong>
                </div>

                <form action="" method="post" id="formUsuari">
                    <label for="email">E-mail</label>
                    <input type="text" name="email" id="email" class="form-ample-100">
                    <br>

                    <label for="password">Contrasenya</label>
                    <input type="password" name="password" id="password" class="form-ample-100">

                    <br>
                    <label for="password">Nom</label>
                    <input type="text" name="nom" id="nom" class="form-ample-100">

                    <br>
                    <label for="password">Cognom</label>
                    <input type="text" name="cognom" id="cognom" class="form-ample-100">

                    <br>
                    <button name="login" id="btnLogin" class="btn-color-negre">Registre</button>

                </form>

            </div>
            <a href="<?php echo BASE_URL; ?>/ca/entrada">Tornar a la pàgina d'entrada</a>
        </div>

    </div>
</div>