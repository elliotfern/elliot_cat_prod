<?php

use App\Utils\Button;
use App\Utils\Routes;

?>
<div id="barraNavegacioContenidor"></div>

<h1>Base de dades Països</h1>
<h2>Llistat complert</h2>

<div class="d-flex flex-wrap gap-2 my-3">
    <?= Button::create('Alta ciutat', Routes::auxiliars()->novaCiutat()) ?>
</div>
</p>

<div id="taulaLlistatCiutats"></div>