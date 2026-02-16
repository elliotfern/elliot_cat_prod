<?php
// Detecta idioma desde la URL: /ca/... /en/... etc.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$parts = array_values(array_filter(explode('/', $path)));
$lang = $parts[0] ?? 'ca';
$allowed = ['ca', 'es', 'en', 'fr', 'it'];
if (!in_array($lang, $allowed, true)) $lang = 'ca';

$isAdmin = isUserAdmin();

// Base pública con idioma
$basePublic = '/' . $lang;

// Base admin (tu intranet)
$baseAdmin = APP_INTRANET; // normalmente "https://elliot.cat/gestio" o similar
// Si APP_INTRANET ya incluye dominio, perfecto. Si no, puedes usar '/gestio'.
?>

<div id="barraNavegacioContenidor"></div>

<h1>Biblioteca de llibres</h1>

<div id="isAdminButton" style="display: none;">
    <?php if ($isAdmin): ?>
        <p>
            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['biblioteca']; ?>/nou-llibre/'" class="button btn-gran btn-secondari">Afegir llibre</button>

            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['persona']; ?>/nova-persona/'" class="button btn-gran btn-secondari">Afegir autor/a</button>
        </p>
    <?php endif; ?>
</div>

<div class="alert alert-success quadre">
    <ul class="llistat">
        <?php if ($isAdmin): ?>
            <!-- Admin: /gestio/... -->
            <li><a href="<?= htmlspecialchars($baseAdmin . $url['biblioteca'] . '/llistat-llibres', ENT_QUOTES) ?>">Llistat de llibres</a></li>
            <li><a href="<?= htmlspecialchars($baseAdmin . $url['biblioteca'] . '/llistat-autors', ENT_QUOTES) ?>">Llistat d'autors/es</a></li>
        <?php else: ?>
            <!-- Públic: /{lang}/... -->
            <li><a href="<?= htmlspecialchars($basePublic . '/biblioteca/llistat-llibres', ENT_QUOTES) ?>">Llistat de llibres</a></li>
            <li><a href="<?= htmlspecialchars($basePublic . '/biblioteca/llistat-autors', ENT_QUOTES) ?>">Llistat d'autors/es</a></li>
        <?php endif; ?>
    </ul>
</div>