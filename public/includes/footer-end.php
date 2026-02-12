<?php
$verFile = dirname(__DIR__) . '/version.txt'; // public/includes -> public/version.txt
$ver = 'dev';

if (is_file($verFile)) {
    $ver = trim((string) file_get_contents($verFile));
    if ($ver === '') $ver = 'dev';
}
?>
<script type="module" src="/dist/bundle.js?v=<?= htmlspecialchars($ver, ENT_QUOTES) ?>"></script>
</body>

</html>