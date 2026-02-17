<div id="barraNavegacioContenidor"></div>

<h1>Base de dades: Història Oberta</h1>
<h2>Llistat de cursos</h2>

<?php if (isUserAdmin()) {
?>
    <button onclick="window.location.href='<?php echo APP_INTRANET . $url['blog']; ?>/nou-curs/'" class="button btn-gran btn-secondari">Nou curs</button>
<?php
} ?>

<div id="cursList" style="margin-top:25px;margin-bottom:30px"></div>