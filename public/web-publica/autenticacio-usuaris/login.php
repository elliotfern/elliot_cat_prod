<div class="min-vh-100 d-flex align-items-center justify-content-center">

  <div class="card shadow-lg border-0"
    style="max-width: 420px; width: 100%; background-color: #c0af77; border: 1px solid #615328;">

    <div class="card-body p-4">

      <h3 class="mb-4 text-center">Accés àrea d'usuaris</h3>

      <div class="alert alert-success d-none" id="okMessage" role="alert">
      </div>
      <div class="alert alert-danger d-none" id="errMessage" role="alert">
      </div>

      <form action="" method="post" id="loginForm">

        <!-- EMAIL -->
        <div class="mb-3">
          <label for="email" class="form-label fw-medium">E-mail</label>
          <input type="email" name="email" id="email" class="form-control" required>
        </div>

        <!-- PASSWORD -->
        <div class="mb-3">
          <label for="password" class="form-label fw-medium">Contrasenya</label>
          <input type="password" name="password" id="password" class="form-control" required>
        </div>

        <!-- BUTTON -->
        <div class="d-grid">
          <button type="submit" id="btnLogin" class="btn btn-dark">
            Entra
          </button>
        </div>

      </form>

      <hr class="my-4">

      <a class="d-block text-center small"
        href="<?php echo BASE_URL; ?>/nou-usuari">
        No estàs registrat? Crea un nou usuari
      </a>

    </div>
  </div>

</div>