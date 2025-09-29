<div class="container contingut">
    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">
            <h1>Comptabilitat: Facturació clients</h1>

            <p><button onclick="window.location.href='<?php echo APP_INTRANET . $url['comptabilitat']; ?>/nova-factura'" class="button btn-gran btn-secondari">Crear factura</button></p>

            <div id="taulaLlistatFactures"></div>

        </div>
    </main>
</div>

<style>
    /* separa los botones dentro del btn-group */
    .btn-group.separat>.btn-petit {
        margin-left: 0 !important;
        /* anula el -1px típico */
        margin-right: .5rem;
        margin-bottom: 0.3rem;
    }

    .btn-group.separat>.btn-petit:last-child {
        margin-right: 0;
    }
</style>