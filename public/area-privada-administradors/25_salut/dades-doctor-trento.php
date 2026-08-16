<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Salut</h1>
<h3>Dades doctor medicina general Trento</h3>
<?php if ($viewModel->isAdmin) : ?>

    <div class="card my-3">
        <div class="card-body">
            <h5 class="card-title mb-2">FAVARA PEDARSI RICCARDO</h5>

            <p class="card-text mb-1">
                V. DEGLI ORTI, 15 - TRENTO (TN), 0461/985394
            </p>

            <p class="card-text small text-muted mb-3">
                PER OGNI ESIGENZA FARE RIFERIMENTO ALLA SEGRETERIA: 0461 985394 -
                SERVIZIO AMBULATORIALE DI SEGRETERIA DISPONIBILE IL LUNEDI', MARTEDI',
                MERCOLEDI' E GIOVEDI' DALLE 8.30 ALLE 19 E IL VENERDI' DALLE 8.30 ALLE
                18 PER FARMACI, CONSULTI TELEFONICI ED APPUNTAMENTI; EMAIL:
                <a href="mailto:ambulatoriozippel@gmail.com">AMBULATORIOZIPPEL@GMAIL.COM</a>
            </p>

            <div class="table-responsive">
                <table class="table table-bordered table-sm text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">Lunedì</th>
                            <th scope="col">Martedì</th>
                            <th scope="col">Mercoledì</th>
                            <th scope="col">Giovedì</th>
                            <th scope="col">Venerdì</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row" class="table-light">Mattina</th>
                            <td>10:30 - 14:00</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>10:30 - 14:00</td>
                        </tr>
                        <tr>
                            <th scope="row" class="table-light">Pomeriggio</th>
                            <td>-</td>
                            <td>12:00 - 15:30</td>
                            <td>12:00 - 15:30</td>
                            <td>12:00 - 15:30</td>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php endif; ?>