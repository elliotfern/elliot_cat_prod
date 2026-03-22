<?php
// slug del libro (según tu router)
$slug = $routeParams[0] ?? '';
?>

<div class="barraNavegacio">
</div>

<div class="container-fluid form">
    <h2>Afegir col·lecció</h2>

    <div class="alert alert-success" id="missatgeOk" style="display:none"></div>
    <div class="alert alert-danger" id="missatgeErr" style="display:none"></div>

    <form id="formAfegirGrup" class="row g-3">
        <input type="hidden" id="id" name="id" value="">

        <div class="col-md-4">
            <label>Nom col·lecció:</label>
            <input class="form-control" type="text" name="nom" id="nom" value="">
        </div>

        <div class="container" style="margin-top:20px">
            <div class="row">
                <div class="col-6 text-left">
                    <a class="btn btn-secondary" href="<?php echo APP_INTRANET . $url['biblioteca']; ?>/llibre-autors/<?php echo htmlspecialchars($slug); ?>">Tornar</a>
                </div>
                <div class="col-6 text-right derecha">
                    <button type="submit" class="btn btn-primary">Afegir</button>
                </div>
            </div>
        </div>
    </form>
</div>