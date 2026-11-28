<?php

use App\Utils\Routes;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Documentació tècnica equip de treball</h1>
<h3>3. Terminal i eines de consola</h3>

<?php if ($viewModel->isAdmin) : ?>

    <div class="container-fluid">

        <div class="alert alert-info">
            Aquesta secció documenta la configuració de Zsh, el terminal,
            les eines de desenvolupament i les utilitats de consola
            instal·lades a l'equip.
        </div>

        <h4 class="mt-4">3.1. Shell i terminal</h4>

        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>Shell predeterminat</th>
                    <td>Zsh</td>
                </tr>
                <tr>
                    <th>Versió Zsh</th>
                    <td>5.9.2</td>
                </tr>
                <tr>
                    <th>Executable</th>
                    <td><code>/usr/bin/zsh</code></td>
                </tr>
                <tr>
                    <th>Terminal gràfic</th>
                    <td>XFCE Terminal 1.2.0</td>
                </tr>
                <tr>
                    <th>Paquet terminal</th>
                    <td>xfce4-terminal 1.2.0-2.fc45</td>
                </tr>
            </tbody>
        </table>

        <h4 class="mt-4">3.2. Oh My Zsh</h4>

        <p>
            S'utilitza Oh My Zsh com a framework de configuració i
            personalització de Zsh.
        </p>

        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>Framework</th>
                    <td>Oh My Zsh</td>
                </tr>
                <tr>
                    <th>Identificador de versió</th>
                    <td><code>c014070</code></td>
                </tr>
                <tr>
                    <th>Últim commit identificat</th>
                    <td>16/11/2026</td>
                </tr>
                <tr>
                    <th>Tema actiu</th>
                    <td><code>robbyrussell</code></td>
                </tr>
                <tr>
                    <th>Plugins actius</th>
                    <td><code>git</code></td>
                </tr>
            </tbody>
        </table>

        <p>La configuració principal es troba a:</p>

        <ul>
            <li><code>~/.zshrc</code></li>
            <li><code>~/.oh-my-zsh/</code></li>
            <li><code>~/.oh-my-zsh/custom/</code></li>
        </ul>

        <p>
            La carpeta <code>custom</code> només conté els fitxers
            d'exemple distribuïts amb Oh My Zsh. No s'han identificat
            temes ni plugins personalitzats propis.
        </p>

        <p>
            El fitxer <code>~/.zshrc</code> conté la configuració
            principal, la càrrega d'Oh My Zsh, la configuració de NVM
            i la integració amb keychain.
        </p>

        <h4 class="mt-4">3.3. Gestió de versions de Node.js</h4>

        <p>
            La gestió de versions de Node.js es realitza mitjançant
            NVM (Node Version Manager).
        </p>

        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>Directori NVM</th>
                    <td><code>~/.nvm</code></td>
                </tr>
                <tr>
                    <th>Node.js predeterminat</th>
                    <td>22.23.1</td>
                </tr>
                <tr>
                    <th>Node.js actiu</th>
                    <td>22.23.1</td>
                </tr>
                <tr>
                    <th>Altres versions instal·lades</th>
                    <td>20.18.0 i 22.22.0</td>
                </tr>
                <tr>
                    <th>npm</th>
                    <td>12.2.3</td>
                </tr>
                <tr>
                    <th>Yarn</th>
                    <td>1.22.22</td>
                </tr>
            </tbody>
        </table>

        <p>
            NVM estableix Node.js 22.23.1 com a versió predeterminada.
            Les diferents versions s'emmagatzemen dins de
            <code>~/.nvm/versions/node/</code>.
        </p>

        <h4 class="mt-4">3.4. Eines de desenvolupament en consola</h4>

        <p>
            Eines principals disponibles des del terminal.
            Les versions detallades de PHP i JavaScript també es
            documenten a les seccions específiques corresponents.
        </p>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Categoria</th>
                    <th>Eina</th>
                    <th>Versió</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Control de versions</td>
                    <td>Git</td>
                    <td>2.56.1</td>
                </tr>
                <tr>
                    <td>GitHub</td>
                    <td>GitHub CLI</td>
                    <td>2.104.0</td>
                </tr>
                <tr>
                    <td>PHP</td>
                    <td>PHP CLI</td>
                    <td>8.5.13</td>
                </tr>
                <tr>
                    <td>Dependències PHP</td>
                    <td>Composer</td>
                    <td>2.11.1</td>
                </tr>
                <tr>
                    <td>Symfony</td>
                    <td>Symfony CLI</td>
                    <td>5.12.0</td>
                </tr>
                <tr>
                    <td>JavaScript</td>
                    <td>Node.js</td>
                    <td>22.23.1</td>
                </tr>
                <tr>
                    <td>JavaScript</td>
                    <td>npm</td>
                    <td>12.2.3</td>
                </tr>
                <tr>
                    <td>JavaScript</td>
                    <td>Yarn</td>
                    <td>1.22.22</td>
                </tr>
                <tr>
                    <td>Python</td>
                    <td>Python 3</td>
                    <td>3.15.2</td>
                </tr>
                <tr>
                    <td>Python</td>
                    <td>pip</td>
                    <td>26.1.2</td>
                </tr>
                <tr>
                    <td>Contenidors</td>
                    <td>Podman</td>
                    <td>6.2.0</td>
                </tr>
            </tbody>
        </table>

        <h4 class="mt-4">3.5. Bases de dades i connexions</h4>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Eina</th>
                    <th>Versió</th>
                    <th>Observacions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>MariaDB client</td>
                    <td>12.3.2</td>
                    <td>Client principal de connexió.</td>
                </tr>
                <tr>
                    <td>MySQL client</td>
                    <td>12.3.2-MariaDB</td>
                    <td>
                        Nom d'executable compatible. Mostra un avís
                        de depreciació; es recomana utilitzar
                        <code>mariadb</code>.
                    </td>
                </tr>
            </tbody>
        </table>

        <h4 class="mt-4">3.6. SSH i transferència de fitxers</h4>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Eina</th>
                    <th>Versió</th>
                    <th>Funció</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>OpenSSH</td>
                    <td>10.6p1</td>
                    <td>Connexions SSH i gestió de claus.</td>
                </tr>
                <tr>
                    <td>keychain</td>
                    <td>3.0.10</td>
                    <td>Integració de claus SSH amb l'entorn de shell.</td>
                </tr>
                <tr>
                    <td>rsync</td>
                    <td>3.5.2</td>
                    <td>Sincronització i transferència de fitxers.</td>
                </tr>
                <tr>
                    <td>rclone</td>
                    <td>1.75.2</td>
                    <td>Transferència i sincronització amb serveis remots.</td>
                </tr>
            </tbody>
        </table>

        <p>
            La configuració de keychain s'invoca des de
            <code>~/.zshrc</code> per carregar la clau SSH
            <code>id_ed25519</code>.
        </p>

        <p>
            Les claus privades no s'han d'incloure mai en còpies
            de documentació ni en repositoris.
        </p>

        <h4 class="mt-4">3.7. Utilitats de xarxa i sistema</h4>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Categoria</th>
                    <th>Eines disponibles</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>HTTP i descàrregues</td>
                    <td>curl, wget</td>
                </tr>
                <tr>
                    <td>Processament JSON</td>
                    <td>jq 1.8.2</td>
                </tr>
                <tr>
                    <td>Diagnòstic de xarxa</td>
                    <td>dig, traceroute, tcpdump</td>
                </tr>
                <tr>
                    <td>Connexions i sockets</td>
                    <td>nc, socat</td>
                </tr>
                <tr>
                    <td>Exploració de fitxers</td>
                    <td>tree 2.3.1</td>
                </tr>
                <tr>
                    <td>Transferència</td>
                    <td>rsync, rclone</td>
                </tr>
            </tbody>
        </table>

        <h4 class="mt-4">3.8. Editors i eines de fitxers</h4>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Eina</th>
                    <th>Versió</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Vim</td>
                    <td>9.2</td>
                </tr>
                <tr>
                    <td>Nano</td>
                    <td>9.2</td>
                </tr>
                <tr>
                    <td>bat</td>
                    <td>0.26.1</td>
                </tr>
                <tr>
                    <td>7-Zip</td>
                    <td>26.06</td>
                </tr>
                <tr>
                    <td>zip / unzip</td>
                    <td>Instal·lats</td>
                </tr>
                <tr>
                    <td>tar</td>
                    <td>Instal·lat</td>
                </tr>
                <tr>
                    <td>zstd</td>
                    <td>Instal·lat</td>
                </tr>
            </tbody>
        </table>

        <h4 class="mt-4">3.9. Configuració del terminal XFCE</h4>

        <p>
            No s'han trobat fitxers de preferències personals a les
            ubicacions habituals inspeccionades:
        </p>

        <ul>
            <li><code>~/.config/xfce4/terminal/terminalrc</code></li>
            <li><code>~/.config/xfce4/terminal/accels.scm</code></li>
        </ul>

        <p>
            Per tant, no s'ha identificat cap configuració específica
            del terminal que sigui necessari preservar en aquesta
            ubicació.
        </p>

        <h4 class="mt-4">3.10. Configuració que cal preservar</h4>

        <p>
            Els elements següents són importants per recuperar
            l'entorn de terminal en un ordinador nou:
        </p>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Element</th>
                    <th>Ubicació</th>
                    <th>Acció</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Configuració Zsh</td>
                    <td><code>~/.zshrc</code></td>
                    <td>Preservar.</td>
                </tr>
                <tr>
                    <td>Oh My Zsh</td>
                    <td><code>~/.oh-my-zsh/</code></td>
                    <td>Reinstal·lar o restaurar.</td>
                </tr>
                <tr>
                    <td>NVM</td>
                    <td><code>~/.nvm/</code></td>
                    <td>
                        Preservar la configuració i reinstal·lar
                        les versions necessàries de Node.js.
                    </td>
                </tr>
                <tr>
                    <td>Claus SSH</td>
                    <td><code>~/.ssh/</code></td>
                    <td>
                        Còpia segura i privada; no incloure en
                        còpies públiques.
                    </td>
                </tr>
            </tbody>
        </table>

        <h4 class="mt-4">3.11. Procediment de restauració</h4>

        <ol>
            <li>Instal·lar Zsh i establir-lo com a shell predeterminat.</li>
            <li>Instal·lar Oh My Zsh.</li>
            <li>Restaurar el fitxer <code>~/.zshrc</code>.</li>
            <li>Instal·lar i configurar keychain i OpenSSH.</li>
            <li>Restaurar les claus SSH mitjançant un procediment segur.</li>
            <li>Instal·lar NVM.</li>
            <li>Instal·lar les versions de Node.js necessàries amb NVM.</li>
            <li>Instal·lar les eines de desenvolupament i utilitats de consola.</li>
            <li>Verificar les versions i el funcionament de les eines.</li>
        </ol>

        <div class="alert alert-warning mt-4">
            <strong>Important:</strong>
            No s'han de copiar contrasenyes, tokens ni claus privades
            dins d'aquesta documentació. Les credencials i les claus
            s'han de gestionar mitjançant còpies de seguretat segures.
        </div>

    </div>

<?php endif; ?>