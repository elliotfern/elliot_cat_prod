 <?php

    use App\Utils\Routes;
    use App\Utils\Button;

    /** @var App\Infrastructure\View\ViewModel $viewModel */
    ?>
 <div id="barraNavegacioContenidor"></div>

 <h1>Base de dades Imatges</h1>
 <h2>Llistat complert</h2>

 <div class="d-flex flex-wrap gap-2 my-3">
     <?= Button::create('Nova imatge', Routes::auxiliars()->nouImatge()) ?>
 </div>

 <div id="avis-alert" class="mb-3"></div>
 <div id="taulaLlistatImatges"></div>