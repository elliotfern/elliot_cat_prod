<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom mb-3">
  <div class="container-fluid">

    <a class="navbar-brand fw-semibold" href="<?php echo APP_INTRANET; ?>">Intranet</a>

    <button class="navbar-toggler" type="button"
      data-bs-toggle="collapse" data-bs-target="#intranetNavbar"
      aria-controls="intranetNavbar" aria-expanded="false"
      aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="intranetNavbar">

      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET; ?>">01. Inici</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>">02. Gestió Comptabilitat i clients</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['persones']; ?>">04. Persones</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['programacio']; ?>">05. Programació</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['projectes']; ?>">06. Gestor projectes</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['contactes']; ?>">07. Agenda contactes</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['biblioteca']; ?>">08. Biblioteca</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['adreces']; ?>">09. Links</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['vault'];  ?>">10. Claus</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['cinema']; ?>">11. Cinema</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['xarxes']; ?>">12. Xarxes socials</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['blog']; ?>">13. Blog</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['rss']; ?>">14. Lector RSS</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['historia']; ?>">15. Historia</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['auxiliars']; ?>">16. Auxiliars</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['viatges']; ?>">17. Viatges</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['usuaris']; ?>">18. Gestió usuaris</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['radio']; ?>">19. Ràdio</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['curriculum']; ?>">20. Currículum</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo APP_INTRANET . $url['agenda']; ?>">21. Agenda</a></li>

      </ul>

      <div class="d-flex gap-2">
        <button class="btn btn-outline-danger btn-sm" id="logoutButton" type="button">
          Tancar sessió
        </button>
      </div>
    </div>
  </div>
</nav>