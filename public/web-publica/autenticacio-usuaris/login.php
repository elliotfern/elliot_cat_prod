<div class="pantallaLogin">
  <div class="card" style="max-width: 400px;">
    <div class="card-body">
      <div class="container">
        <h3>Accés àrea d'usuaris</h3>
        <div class="alert alert-success" id="loginMessageOk" style="display:none" role="alert">
        </div>

        <div class="alert alert-danger" id="loginMessageErr" style="display:none" role="alert">
        </div>

        <form action="" method="post" id="loginForm">
          <label for="email">E-mail</label>
          <input type="text" name="email" id="email" class="form-ample-100">
          <br>

          <label for="password">Contrasenya</label>
          <input type="password" name="password" id="password" class="form-ample-100">
          <br>
          <button name="login" id="btnLogin" class="btn-color-negre">Entra</button>
        </form>

      </div>
      <a href="<?php echo BASE_URL; ?>/nou-usuari">No estàs registrat al web? Clica aquí per crear un nou usuari</a>
    </div>

  </div>
</div>