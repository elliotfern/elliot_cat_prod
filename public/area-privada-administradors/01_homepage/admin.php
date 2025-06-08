<div class="container contenidor">

    <div id="barraNavegacioContenidor"></div>



    <h1>Intranet</h1>
    <div id="isAdminButton" style="display: none;">
        <?php if (isUserAdmin()) { ?>
            <p>

            </p>

            <div class="alert alert-success quadre">
                <ul class="llistat">
                    <li></li>
                </ul>
            </div>

        <?php } else {
            // Código que se ejecuta si la condición es falsa (opcional)
        } ?>

    </div>

</div>
<style>
    .contenidor {
        width: 100%;
        background-color: #ffffff;
        padding: 25px;
    }
</style>