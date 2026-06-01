<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <h2>Legalització títol llicenciatura</h2>
    <?php if (isUserAdmin()) { ?>

        <h4>Documents necessaris:</h4>

        <ul>
            <li>Copia del diploma di Master in lingua originale</li>
            <li>Certificato in italiano o in inglese attestante il Master</li>
            <li>Certificato in italiano o in inglese attestante gli esami superati durante il Master</li>
            <li>Copia del diploma di Bachelor in lingua originale</li>
            <li>Certificato in italiano o in inglese attestante il Bachelor</li>
            <li>Certificato in italiano o in inglese attestante gli esami superati durante il Bachelor</li>
            <li>Certificati attestanti altri titoli eventuali</li>
            <li>Copia del documento d'identità</li>
            <li><a href="<?php echo APP_INTRANET . $url['taulell_pendents']; ?>/declaracio-valor-titol">Dichiarazione di valore del titolo di laurea (se in suo possesso)</a></li>
            <li>Diploma Supplement (se in suo possesso)</li>
        </ul>

        <p><strong>Ufficio Offerta Formativa e Gestione Studenti</strong><br>
            Lettere e Filosofia, Sociologia e Ricerca Sociale<br>
            Divisione Servizi Didattici e Studenti - Polo Città<br>
            Direzione Didattica e Servizi agli Studenti<br>
            Università di Trento</p>
        <p><a href="mailto:supportostudentilettsoc@unitn.it">supportostudentilettsoc@unitn.it</a></p>

        <p>
            via Tommaso Gar, 14 - 38122 Trento (Italy)<br>
            telefono - phone +39 0461 28 2983 lun-ven / Mon-Fri 9:30-10:30<br>
            sportello Zoom con prenotazione online - Zoom desk with online booking mar / Tue 11-12<br>
            sportello in presenza con prenotazione online - Helpdesk in presence with online booking mer / Wed 14:30-16
        </p>
    <?php } else {
        // Código que se ejecuta si la condición es falsa (opcional)
    } ?>

</div>