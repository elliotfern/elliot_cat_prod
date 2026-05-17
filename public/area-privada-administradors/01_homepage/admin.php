<?php

use App\Utils\Url;

?>

<div id="barraNavegacioContenidor"></div>

<h1>Intranet</h1>
<?php if (isUserAdmin()) { ?>
    <div class="alert alert-success">
        <h4>Taulell temes pendents</h4>
        <ul>
            <li><a href="<?php echo Url::intranet('taulell_legalitzacio'); ?>">Legalització títol llicenciatura d'història</a></li>
        </ul>

        <h4>Treball:</h4>

        <ul>
            <li><a href="<?php echo Url::intranet('comptabilitat'); ?>">2. Gestió Comptabilitat</a></li>
            <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/llistat-clients">2.1 Gestió clients</a></li>
            <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/llistat-pressupostos">2.2 Gestió pressupostos</a></li>
            <li><a href="<?php echo Url::intranet('projectes'); ?>">6. Gestor de projectes</a></li>
            <li><a href="<?php echo Url::intranet('programacio'); ?>">5. Recursos programació web</a></li>
            <li><a href="<?php echo Url::intranet('vault'); ?>">10. Claus d'accés</a></li>
        </ul>

        <h4>Personal:</h4>
        <ul>
            <li><a href="<?php echo Url::intranet('persones'); ?>">4. Base de dades: Persones</a></li>
            <li><a href="<?php echo Url::intranet('biblioteca'); ?>">8. Base de dades: Biblioteca</a></li>
            <li><a href="<?php echo Url::intranet('cinema'); ?>">11. Base de dades: Cinema i sèries</a></li>
            <li><a href="<?php echo Url::intranet('viatges'); ?>">17. Base de dades: Viatges</a></li>
            <li><a href="<?php echo Url::intranet('contactes'); ?>">7. Agenda de contactes</a></li>
            <li><a href="<?php echo Url::intranet('adreces'); ?>">9. Enllaços d'interés</a></li>
            <li><a href="<?php echo Url::intranet('xarxes'); ?>">12. Xarxes socials</a></li>
            <li><a href="<?php echo Url::intranet('blog'); ?>">13. Blog</a></li>
            <li><a href="<?php echo Url::intranet('rss'); ?>">14. Lector RSS</a></li>
            <li><a href="<?php echo Url::intranet('historia'); ?>">15. Base de dades Història</a></li>
            <li><a href="<?php echo Url::intranet('auxiliars'); ?>">16. Taules auxiliars</a></li>
            <li><a href="<?php echo Url::intranet('usuaris'); ?>">18. Gestió usuaris</a></li>
            <li><a href="<?php echo Url::intranet('radio'); ?>">19. Ràdio online</a></li>
            <li><a href="<?php echo Url::intranet('curriculum'); ?>">20. Currículum</a></li>
            <li><a href="<?php echo Url::intranet('agenda'); ?>">21. Agenda</a></li>
            <li><a href="<?php echo Url::intranet('taulell-pendents'); ?>">22. Taulell temes pendents</a></li>
        </ul>
    </div>

<?php } else {
    // Código que se ejecuta si la condición es falsa (opcional)
} ?>