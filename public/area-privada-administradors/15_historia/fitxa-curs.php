<div id="barraNavegacioContenidor"></div>

<h1>Base de dades: Història Oberta</h1>
<div id="infoCurs"></div>

<?php if (isUserAdmin()) {
?>
    <button onclick="window.location.href='<?php echo APP_INTRANET . $url['blog']; ?>/nou-curs/'" class="button btn-gran btn-secondari">Nou article</button>
<?php
} ?>

<div id="llistatArticles" style="margin-top:25px;margin-bottom:30px"></div>