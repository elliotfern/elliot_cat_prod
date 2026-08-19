 <?php

    use App\Utils\Routes;
    use App\Utils\Button;

    /** @var App\Infrastructure\View\ViewModel $viewModel */
    ?>
 <div id="barraNavegacioContenidor"></div>

 <h1>Base de dades: Imatges</h1>
 <h2>Llistat Galeries d'Imatges</h2>

 <div class="d-flex flex-wrap gap-2 my-3">
     <?= Button::create('Nova Galeria imatges', Routes::auxiliars()->novaGaleriaImatges()) ?>
 </div>

 <div id="taulaLlistatGaleriesImatges"></div>