<div class="barraNavegacio"></div>


<h1>Base de dades d'Història</h1>
<p>
    <button onclick="window.location.href='<?php echo APP_INTRANET . $url['historia']; ?>/nou-llibre/'" class="button btn-gran btn-secondari">Afegir esdeveniment</button>

    <button onclick="window.location.href='<?php echo APP_INTRANET . $url['persona']; ?>/nova-persona/'" class="button btn-gran btn-secondari">Afegir persona</button>
</p>

<div class="alert alert-success quadre">
    <ul class="llistat">
        <li> <a href="<?php echo APP_INTRANET . $url['historia']; ?>/llistat-articles">Llistat de articles Història Oberta</a></li>
        <li> <a href="<?php echo APP_INTRANET . $url['historia']; ?>/llistat-cursos">Llistat de cursos Història Oberta</a></li>
        <li> <a href="<?php echo APP_INTRANET . $url['persones']; ?>/llistat-persones">Llistat de persones</a></li>
        <li><a href="<?php echo APP_INTRANET . $url['historia']; ?>/llistat-organitzacions">Llistat d'organitzacions</a></li>
        <li><a href="<?php echo APP_INTRANET . $url['historia']; ?>/llistat-esdeveniments">Llistat d'esdeveniments</a></li>
    </ul>
</div>