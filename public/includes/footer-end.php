<?php
$verFile = dirname(__DIR__) . '/version.txt';
$ver = 'dev';

if (is_file($verFile)) {
    $ver = trim((string) file_get_contents($verFile));
    if ($ver === '') $ver = 'dev';
}
?>

<script src="/dist/bundle.js?v=<?= htmlspecialchars($ver, ENT_QUOTES) ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>