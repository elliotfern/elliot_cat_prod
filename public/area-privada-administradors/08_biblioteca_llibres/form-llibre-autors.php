<?php
// slug del libro (según tu router)
$slug = $routeParams[0] ?? '';
?>

<div class="barraNavegacio">
</div>

<div class="container-fluid form">
    <h2>Afegir autor al llibre</h2>
    <h4 id="bookTitle"></h4>

    <div class="alert alert-success" id="missatgeOk" style="display:none"></div>
    <div class="alert alert-danger" id="missatgeErr" style="display:none"></div>

    <form id="formAfegirAutor" class="row g-3">
        <input type="hidden" id="llibre_slug" name="llibre_slug" value="<?php echo htmlspecialchars($slug); ?>">

        <div class="col-md-4">
            <label>Autor:</label>
            <select class="form-select" name="autor_id" id="autor_id"></select>
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

<script>
    // helpers alerts
    function hideAlerts() {
        const ok = document.getElementById('missatgeOk');
        const err = document.getElementById('missatgeErr');
        if (ok) {
            ok.style.display = 'none';
            ok.innerHTML = '';
        }
        if (err) {
            err.style.display = 'none';
            err.innerHTML = '';
        }
    }

    function showOk(message) {
        const ok = document.getElementById('missatgeOk');
        if (!ok) return;
        ok.style.display = 'block';
        ok.innerHTML = `<h4 class="alert-heading"><strong>${message || 'Operació correctament.'}</strong></h4>`;
        ok.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    function showErr(message, errorsObj) {
        const err = document.getElementById('missatgeErr');
        if (!err) return;
        const items = [];
        if (errorsObj && typeof errorsObj === 'object') {
            for (const [field, reason] of Object.entries(errorsObj)) {
                items.push(`<li><strong>${field}</strong>: ${String(reason)}</li>`);
            }
        }
        err.style.display = 'block';
        err.innerHTML = `
      <h4 class="alert-heading"><strong>${message || "S'ha produït un error."}</strong></h4>
      ${items.length ? `<ul class="mb-0">${items.join('')}</ul>` : ''}
    `;
        err.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    async function fillSelectAutors() {
        // Usa tu endpoint existente (ya lo tienes): /api/biblioteca/get/?type=autors
        const res = await fetch('/api/biblioteca/get/?type=autors', {
            headers: {
                'Accept': 'application/json'
            }
        });
        const json = await res.json();

        const items = Array.isArray(json?.data) ? json.data : (Array.isArray(json) ? json : []);
        const select = document.getElementById('autor_id');
        if (!select) return;

        select.innerHTML = '';
        const ph = document.createElement('option');
        ph.value = '';
        ph.textContent = '— Selecciona —';
        select.appendChild(ph);

        items.forEach((a) => {
            const opt = document.createElement('option');
            opt.value = a.id; // UUID string
            // ajusta el label al campo que devuelva tu api de autors
            const nom = (a.AutNom ?? a.nom ?? '').toString();
            const cognoms = (a.AutCognom1 ?? a.cognoms ?? '').toString();
            opt.textContent = (nom + ' ' + cognoms).trim() || (a.slug ?? a.id);
            select.appendChild(opt);
        });
    }

    async function loadBookTitle() {
        const slug = document.getElementById('llibre_slug')?.value || '';
        if (!slug) return;

        const res = await fetch(`/api/biblioteca/get/?llibreSlug=${encodeURIComponent(slug)}`, {
            headers: {
                'Accept': 'application/json'
            }
        });
        const json = await res.json();

        const data = json?.data ?? json;
        const h4 = document.getElementById('bookTitle');
        if (h4) h4.textContent = data?.titol ? `Llibre: ${data.titol}` : '';
    }

    async function submitAfegirAutor(e) {
        e.preventDefault();
        hideAlerts();

        const slug = document.getElementById('llibre_slug')?.value || '';
        const autorId = document.getElementById('autor_id')?.value || '';

        const payload = {
            llibre_slug: slug,
            autor_id: autorId
        };

        try {
            const res = await fetch('/api/biblioteca/post/?llibreAutor', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload),
            });

            const json = await res.json().catch(() => null);
            if (!json) {
                showErr('Resposta invàlida del servidor', {});
                return;
            }

            if (json.status === 'success') {
                showOk(json.message);
            } else {
                showErr(json.message, json.errors);
            }
        } catch (err) {
            console.error(err);
            showErr("Error de connexió amb el servidor", {});
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadBookTitle();
        fillSelectAutors();
        const form = document.getElementById('formAfegirAutor');
        if (form) form.addEventListener('submit', submitAfegirAutor);
    });
</script>