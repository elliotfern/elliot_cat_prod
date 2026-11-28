<?php

use App\Utils\Routes;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Documentació tècnica equip de treball</h1>
<h3>2. Entorn gràfic i aplicacions personals</h3>

<?php if ($viewModel->isAdmin) : ?>

    <!-- Capçalera -->
    <header class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge text-bg-primary">Entorn d'escriptori</span>
            <span class="badge text-bg-success">Configuració identificada</span>
        </div>

        <p class="text-body-secondary mb-0">
            Documentació de l'entorn gràfic XFCE, la configuració visual,
            els panells, les dreceres de teclat, les aplicacions personals
            i els serveis que s'inicien automàticament.
        </p>
    </header>

    <hr>

    <!-- Entorn gràfic -->
    <section class="mb-5" id="entorn-grafic">
        <h2 class="h4 mb-3">1. Entorn gràfic</h2>

        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <tbody>
                    <tr>
                        <th scope="row" class="w-25">Entorn d'escriptori</th>
                        <td>XFCE</td>
                    </tr>
                    <tr>
                        <th scope="row">Servidor gràfic</th>
                        <td>X11</td>
                    </tr>
                    <tr>
                        <th scope="row">Gestor de sessions</th>
                        <td>LightDM</td>
                    </tr>
                    <tr>
                        <th scope="row">Versió de LightDM</th>
                        <td>1.33.1-7.fc45</td>
                    </tr>
                    <tr>
                        <th scope="row">Resolució actual</th>
                        <td>1920 × 1080 píxels</td>
                    </tr>
                    <tr>
                        <th scope="row">Pantalla principal</th>
                        <td>eDP-1 (pantalla integrada)</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="h6">Components principals d'XFCE</h3>

        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Component</th>
                        <th>Versió</th>
                        <th>Funció</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>xfce4-session</td>
                        <td>4.20.4</td>
                        <td>Gestió de sessions</td>
                    </tr>
                    <tr>
                        <td>xfce4-panel</td>
                        <td>4.20.8</td>
                        <td>Panells i llançadors</td>
                    </tr>
                    <tr>
                        <td>xfwm4</td>
                        <td>4.20.0</td>
                        <td>Gestor de finestres</td>
                    </tr>
                    <tr>
                        <td>xfdesktop</td>
                        <td>4.20.2</td>
                        <td>Escriptori i fons de pantalla</td>
                    </tr>
                    <tr>
                        <td>xfce4-settings</td>
                        <td>4.20.5</td>
                        <td>Configuració de l'entorn</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Configuració visual -->
    <section class="mb-5" id="configuracio-visual">
        <h2 class="h4 mb-3">2. Configuració visual</h2>

        <h3 class="h6">Temes i aparença</h3>

        <ul>
            <li>Tema GTK: <code>Adwaita-dark</code>.</li>
            <li>Tema de finestres XFWM: <code>Default</code>.</li>
            <li>Tema d'icones: no s'ha identificat una selecció explícita a la configuració d'XFCE.</li>
        </ul>

        <h3 class="h6">Fons de pantalla</h3>

        <ul>
            <li>Pantalla integrada eDP-1: <code>default-dark-16_10.jxl</code>.</li>
            <li>Perfil guardat per a HDMI-2: <code>default-dark-16_9.jxl</code>.</li>
        </ul>

        <h3 class="h6">Perfils de pantalla</h3>

        <p>
            La configuració conserva un perfil per a un monitor extern
            AOC International de 27 polzades, amb resolució de 1920 × 1080
            i freqüència de 60 Hz.
        </p>

        <p class="text-body-secondary small">
            Aquest perfil està desat a la configuració, però el monitor
            extern no estava connectat durant la inspecció.
        </p>
    </section>

    <!-- Panells i espais de treball -->
    <section class="mb-5" id="panells-escriptoris">
        <h2 class="h4 mb-3">3. Panells i espais de treball</h2>

        <h3 class="h6">Panell superior</h3>

        <ul>
            <li>Posició: part superior de la pantalla.</li>
            <li>Mida: 33 píxels.</li>
            <li>Mida de les icones: 17 píxels.</li>
            <li>Longitud: 100 %.</li>
            <li>Panell bloquejat.</li>
        </ul>

        <p>Conté els elements següents:</p>

        <ul>
            <li>Menú d'aplicacions.</li>
            <li>Llista de finestres obertes.</li>
            <li>Separador i paginador d'espais de treball.</li>
            <li>Safata del sistema.</li>
            <li>Rellotge.</li>
            <li>Accions de sessió.</li>
            <li>Control de volum.</li>
        </ul>

        <h3 class="h6">Panell inferior</h3>

        <ul>
            <li>Posició: part inferior de la pantalla.</li>
            <li>Mida: 44 píxels.</li>
            <li>Longitud: aproximadament 96,48 %.</li>
            <li>Una sola fila.</li>
            <li>Marge: 6 píxels.</li>
            <li>Panell bloquejat.</li>
            <li>Conté diversos llançadors d'aplicacions.</li>
        </ul>

        <h3 class="h6">Espais de treball</h3>

        <p>Configurats quatre espais de treball:</p>

        <ul>
            <li>Espai de treball 1</li>
            <li>Espai de treball 2</li>
            <li>Espai de treball 3</li>
            <li>Espai de treball 4</li>
        </ul>
    </section>

    <!-- Teclat i finestres -->
    <section class="mb-5" id="teclat-finestres">
        <h2 class="h4 mb-3">4. Teclat i gestor de finestres</h2>

        <h3 class="h6">Dreceres de teclat personalitzades</h3>

        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Drecera</th>
                        <th>Acció</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><kbd>Ctrl</kbd> + <kbd>Alt</kbd> + <kbd>T</kbd></td>
                        <td>Obrir el terminal</td>
                    </tr>
                    <tr>
                        <td><kbd>Super</kbd> + <kbd>E</kbd></td>
                        <td>Obrir Thunar</td>
                    </tr>
                    <tr>
                        <td><kbd>Super</kbd> + <kbd>R</kbd></td>
                        <td>Obrir el cercador d'aplicacions en mode comandes</td>
                    </tr>
                    <tr>
                        <td><kbd>Impr Pant</kbd></td>
                        <td>Captura de pantalla</td>
                    </tr>
                    <tr>
                        <td><kbd>Ctrl</kbd> + <kbd>Alt</kbd> + <kbd>F</kbd></td>
                        <td>Obrir Thunar</td>
                    </tr>
                    <tr>
                        <td><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>Esc</kbd></td>
                        <td>Obrir el gestor de tasques</td>
                    </tr>
                    <tr>
                        <td><kbd>Ctrl</kbd> + <kbd>Alt</kbd> + <kbd>Supr</kbd></td>
                        <td>Tancar la sessió</td>
                    </tr>
                    <tr>
                        <td><kbd>Alt</kbd> + <kbd>F1</kbd></td>
                        <td>Obrir el menú d'aplicacions</td>
                    </tr>
                    <tr>
                        <td><kbd>Alt</kbd> + <kbd>F2</kbd></td>
                        <td>Obrir el cercador d'aplicacions</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-body-secondary small">
            També hi ha dreceres multimèdia configurades per controlar
            el reproductor Pragha.
        </p>

        <h3 class="h6">Configuració del gestor de finestres</h3>

        <ul>
            <li>Focus de finestres: en fer clic.</li>
            <li>Doble clic a la barra de títol: maximitzar.</li>
            <li>Quatre espais de treball.</li>
            <li>Decoració de finestres: tema <code>Default</code>.</li>
            <li>Títols de finestres centrats.</li>
            <li>Composició gràfica (compositor): desactivada.</li>
        </ul>
    </section>

    <!-- Aplicacions -->
    <section class="mb-5" id="aplicacions-personals">
        <h2 class="h4 mb-3">5. Aplicacions personals instal·lades</h2>

        <p>
            Relació de les principals aplicacions gràfiques identificades
            al sistema. S'hi inclouen les eines d'ús personal i de treball,
            no les biblioteques ni les dependències internes dels paquets.
        </p>

        <h3 class="h6">Navegadors i comunicació</h3>
        <ul>
            <li>Firefox.</li>
            <li>Google Chrome.</li>
            <li>Thunderbird.</li>
        </ul>

        <h3 class="h6">Desenvolupament</h3>
        <ul>
            <li>Visual Studio Code.</li>
        </ul>

        <h3 class="h6">Ofimàtica</h3>
        <ul>
            <li>LibreOffice Writer.</li>
            <li>LibreOffice Calc.</li>
            <li>LibreOffice Impress.</li>
            <li>LibreOffice Draw.</li>
        </ul>

        <h3 class="h6">Disseny i imatge</h3>
        <ul>
            <li>GIMP.</li>
            <li>Inkscape.</li>
            <li>Scribus.</li>
            <li>Loupe (visor d'imatges).</li>
            <li>Snapshot (càmera).</li>
        </ul>

        <h3 class="h6">Documents i gestió de fitxers</h3>
        <ul>
            <li>Calibre.</li>
            <li>Evince (visor de documents).</li>
            <li>Simple Scan (escàner).</li>
            <li>FileZilla.</li>
            <li>Xarchiver.</li>
        </ul>

        <h3 class="h6">Multimèdia i transferències</h3>
        <ul>
            <li>VLC media player.</li>
            <li>Transmission.</li>
        </ul>

        <h3 class="h6">Sistema i manteniment</h3>
        <ul>
            <li>GParted.</li>
            <li>Hardinfo2.</li>
            <li>Baobab (analitzador d'ús del disc).</li>
            <li>Catfish (cercador de fitxers).</li>
            <li>Galculator.</li>
        </ul>

        <h3 class="h6">Seguretat i configuració</h3>
        <ul>
            <li>Seahorse (contrasenyes i claus).</li>
            <li>Firewall Configuration.</li>
            <li>dnfdragora (gestor gràfic de paquets).</li>
            <li>PulseAudio Volume Control.</li>
        </ul>

        <div class="alert alert-info">
            <strong>Observació:</strong>
            no s'han detectat aplicacions Flatpak ni fitxers AppImage
            durant l'inventari.
        </div>

        <p class="text-body-secondary small">
            També s'han identificat dos executables auxiliars a
            <code>~/.local/bin</code>: <code>tqdm</code> i
            <code>pyjson5</code>. Es documentaran, si escau, a l'apartat
            d'eines de desenvolupament.
        </p>
    </section>

    <!-- Inici automàtic i configuració -->
    <section class="mb-5" id="inici-automatic">
        <h2 class="h4 mb-3">6. Inici automàtic i fitxers de configuració</h2>

        <h3 class="h6">Serveis i aplicacions d'inici automàtic</h3>

        <p>
            XFCE inicia automàticament diversos serveis i components
            necessaris per al funcionament de l'entorn gràfic.
        </p>

        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Component</th>
                        <th>Funció</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Blueman</td>
                        <td>Gestió de Bluetooth</td>
                    </tr>
                    <tr>
                        <td>GNOME Keyring</td>
                        <td>Gestió de credencials i claus</td>
                    </tr>
                    <tr>
                        <td>NetworkManager Applet</td>
                        <td>Gestió de connexions de xarxa</td>
                    </tr>
                    <tr>
                        <td>XFCE Clipman</td>
                        <td>Gestor del porta-retalls</td>
                    </tr>
                    <tr>
                        <td>XFCE Notifyd</td>
                        <td>Notificacions de l'escriptori</td>
                    </tr>
                    <tr>
                        <td>XFCE Power Manager</td>
                        <td>Gestió d'energia</td>
                    </tr>
                    <tr>
                        <td>XFCE Screensaver</td>
                        <td>Bloqueig i protecció de pantalla</td>
                    </tr>
                    <tr>
                        <td>XFCE Polkit</td>
                        <td>Autenticació d'operacions privilegiades</td>
                    </tr>
                    <tr>
                        <td>XFSettingsd</td>
                        <td>Gestió de la configuració XFCE</td>
                    </tr>
                    <tr>
                        <td>Seahorse / GNOME Keyring</td>
                        <td>Suport per a credencials i claus</td>
                    </tr>
                    <tr>
                        <td>Orca</td>
                        <td>Accessibilitat i lector de pantalla</td>
                    </tr>
                    <tr>
                        <td>Geoclue</td>
                        <td>Serveis de localització</td>
                    </tr>
                    <tr>
                        <td>Evolution Alarm Notify</td>
                        <td>Notificacions de calendari</td>
                    </tr>
                    <tr>
                        <td>VMware User</td>
                        <td>Integració amb entorns VMware</td>
                    </tr>
                    <tr>
                        <td>SELinux Troubleshooter</td>
                        <td>Notificacions relacionades amb SELinux</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="h6">Ubicació de la configuració d'XFCE</h3>

        <p>
            La configuració personal de l'entorn gràfic es troba
            principalment al directori:
        </p>

        <p><code>~/.config/xfce4/</code></p>

        <p>Els fitxers principals identificats són:</p>

        <ul>
            <li><code>xfconf/xfce-perchannel-xml/</code>: configuració dels canals XFCE.</li>
            <li><code>panel/</code>: configuració dels panells i llançadors.</li>
            <li><code>desktop/</code>: configuració de l'escriptori i dreceres.</li>
            <li><code>xfce4-screenshooter/</code>: configuració de captures de pantalla.</li>
            <li><code>helpers.rc</code>: configuració d'aplicacions auxiliars.</li>
        </ul>

        <h3 class="h6">Configuracions addicionals identificades</h3>

        <ul>
            <li><code>xfce4-panel.xml</code>: panells, complements i rellotge.</li>
            <li><code>xfce4-keyboard-shortcuts.xml</code>: dreceres de teclat.</li>
            <li><code>xfwm4.xml</code>: gestor de finestres.</li>
            <li><code>xfce4-desktop.xml</code>: escriptori i fons de pantalla.</li>
            <li><code>displays.xml</code>: perfils de pantalla.</li>
            <li><code>xsettings.xml</code>: aparença i preferències gràfiques.</li>
        </ul>

        <div class="alert alert-warning">
            <strong>Important:</strong>
            el directori <code>~/.config/xfce4/</code> conté bona part
            de la configuració personalitzada de l'entorn. Cal incloure'l
            a les còpies de seguretat per facilitar-ne la restauració
            en un ordinador nou.
        </div>

        <h3 class="h6">Altres preferències identificades</h3>

        <ul>
            <li>Mode de presentació del gestor d'energia activat.</li>
            <li>Icona de safata del gestor d'energia desactivada.</li>
            <li>Notificacions generals del gestor d'energia desactivades.</li>
            <li>Thunar utilitza la vista detallada com a darrera vista.</li>
            <li>Els fitxers ocults no es mostren per defecte.</li>
            <li>Zona horària configurada al rellotge del panell: <code>Europe/Rome</code>.</li>
        </ul>

        <p class="text-body-secondary small">
            La zona horària del rellotge s'ha identificat a la configuració
            del panell i queda pendent de verificar si coincideix amb la
            zona horària general del sistema.
        </p>
    </section>

<?php endif; ?>