<?php

use App\Utils\Routes;
use App\Utils\Button;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Ràdio online</h1>

<?php if ($viewModel->isAdmin) : ?>
    <div class="alert alert-success quadre">
        <ul class="llistat">
            <li><a href="<?= Routes::radio()->rairadiotre() ?>">Rai Radio 3</a></li>
            <li><a href="<?= Routes::radio()->catmusica() ?>">Catalunya Música</a></li>
            <li><a href="<?= Routes::radio()->icatfm() ?>">iCatfm</a></li>
            <li><a href="<?= Routes::radio()->catinfo() ?>">Catalunya Informació</a></li>
            <li><a href="<?= Routes::radio()->bbc4() ?>">BBC 4</a></li>
            <li><a href="<?= Routes::radio()->bbc6() ?>">BBC 6</a></li>
            <li><a href="<?= Routes::radio()->franceculture() ?>">France Culture</a></li>
            <li><a href="<?= Routes::radio()->franceinter() ?>">France Inter</a></li>
            <li><a href="<?= Routes::radio()->francemusique() ?>">France Musique</a></li>
            <li><a href="<?= Routes::radio()->radiomunicipalterrassa() ?>">Ràdio Municipal de Terrassa</a></li>
        </ul>
    </div>
<?php endif; ?>