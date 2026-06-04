 <?php

    /** @var App\Infrastructure\View\ViewModel $viewModel */
    ?>
 <div id="barraNavegacioContenidor"></div>

 <h1>Base de dades Imatges</h1>
 <h2>Llistat complert</h2>

 <?php if ($viewModel->isAdmin) : ?>
     <p>
         <button onclick="window.location.href='<?php echo $url['auxiliars']; ?>/nova-imatge/'" class="button btn-gran btn-secondari">Afegir imatge</button>
     </p>
 <?php endif; ?>
 </div>

 <div id="taulaLlistatImatges"></div>