<nav id="intranetNav" class="navbar navbar-light bg-light border-bottom" style="position: sticky; z-index: 1020;">
  <div class="container-fluid">
    <a class="navbar-brand fw-semibold" href="<?= APP_INTRANET ?>">Intranet</a>

    <!-- Favoritos (desktop) -->
    <div class="d-none d-lg-flex gap-2">
      <a class="btn btn-sm btn-outline-secondary" href="<?= APP_INTRANET ?>">Inici</a>
      <a class="btn btn-sm btn-outline-secondary" href="<?= APP_INTRANET . $url['projectes'] ?>">Projectes</a>
      <a class="btn btn-sm btn-outline-secondary" href="<?= APP_INTRANET . $url['programacio'] ?>">Programació</a>
      <a class="btn btn-sm btn-outline-secondary" href="<?= APP_INTRANET . $url['comptabilitat'] ?>">Comptabilitat</a>
      <a class="btn btn-sm btn-outline-secondary" href="<?= APP_INTRANET . $url['agenda'] ?>">Agenda</a>
    </div>

    <div class="d-flex gap-2 align-items-center">
      <button class="btn btn-sm btn-outline-primary" type="button"
        data-bs-toggle="offcanvas" data-bs-target="#menuOffcanvas"
        aria-controls="menuOffcanvas">
        ☰ Més
      </button>

      <button class="btn btn-sm btn-outline-danger" id="logoutButton" type="button">
        Tancar sessió
      </button>
    </div>
  </div>
</nav>

<div class="offcanvas offcanvas-start" tabindex="-1" id="menuOffcanvas" aria-labelledby="menuOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="menuOffcanvasLabel">Menú</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">

    <!-- Aquí ya pondremos TODOS tus enlaces agrupados -->
    <div class="list-group mb-3">
      <div class="small text-muted mb-2">Gestió</div>
      <a class="list-group-item list-group-item-action" href="<?= APP_INTRANET . $url['comptabilitat'] ?>">Comptabilitat i clients</a>
      <a class="list-group-item list-group-item-action" href="<?= APP_INTRANET . $url['usuaris'] ?>">Gestió usuaris</a>
      <a class="list-group-item list-group-item-action" href="<?= APP_INTRANET . $url['projectes'] ?>">Gestor projectes</a>
    </div>

    <div class="list-group mb-3">
      <div class="small text-muted mb-2">CRM / Contactes</div>
      <a class="list-group-item list-group-item-action" href="<?= APP_INTRANET . $url['persones'] ?>">Persones</a>
      <a class="list-group-item list-group-item-action" href="<?= APP_INTRANET . $url['contactes'] ?>">Agenda contactes</a>
      <a class="list-group-item list-group-item-action" href="<?= APP_INTRANET . $url['agenda'] ?>">Agenda</a>
    </div>

    <div class="list-group mb-3">
      <div class="small text-muted mb-2">Contingut</div>
      <a class="list-group-item list-group-item-action" href="<?= APP_INTRANET . $url['blog'] ?>">Blog</a>
      <a class="list-group-item list-group-item-action" href="<?= APP_INTRANET . $url['rss'] ?>">Lector RSS</a>
      <a class="list-group-item list-group-item-action" href="<?= APP_INTRANET . $url['historia'] ?>">Historia</a>
      <a class="list-group-item list-group-item-action" href="<?= APP_INTRANET . $url['biblioteca'] ?>">Biblioteca</a>
    </div>

    <div class="list-group mb-3">
      <div class="small text-muted mb-2">Eines</div>
      <a class="list-group-item list-group-item-action" href="<?= APP_INTRANET . $url['adreces'] ?>">Links</a>
      <a class="list-group-item list-group-item-action" href="<?= APP_INTRANET . $url['vault'] ?>">Claus</a>
      <a class="list-group-item list-group-item-action" href="<?= APP_INTRANET . $url['auxiliars'] ?>">Auxiliars</a>
    </div>

    <div class="list-group">
      <div class="small text-muted mb-2">Oci</div>
      <a class="list-group-item list-group-item-action" href="<?= APP_INTRANET . $url['cinema'] ?>">Cinema</a>
      <a class="list-group-item list-group-item-action" href="<?= APP_INTRANET . $url['radio'] ?>">Ràdio</a>
      <a class="list-group-item list-group-item-action" href="<?= APP_INTRANET . $url['viatges'] ?>">Viatges</a>
      <a class="list-group-item list-group-item-action" href="<?= APP_INTRANET . $url['xarxes'] ?>">Xarxes socials</a>
    </div>

  </div>
</div>