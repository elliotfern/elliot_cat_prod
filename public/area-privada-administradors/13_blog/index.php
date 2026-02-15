<div id="barraNavegacioContenidor"></div>
<h1>Blog</h1>

<?php if (isUserAdmin()) {
?>
    <button onclick="window.location.href='<?php echo APP_INTRANET . $url['blog']; ?>/nou-article/'" class="button btn-gran btn-secondari">Nou article</button>
<?php
} ?>

<div id="articleList"></div>