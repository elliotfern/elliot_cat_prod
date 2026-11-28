<?php

use App\Utils\Routes;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Documentació tècnica equip de treball</h1>
<h3>4. Xarxa i seguretat</h3>

<?php if ($viewModel->isAdmin) : ?>

<div class="container-fluid px-0">

    <p class="text-muted">
        Configuració de xarxa, resolució DNS, tallafoc, SELinux,
        connexions SSH i certificats digitals del sistema.
    </p>

    <!-- XARXA -->
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">4.1. Configuració de xarxa</h4>
        </div>
        <div class="card-body">

            <h5>Estat general</h5>
            <ul>
                <li>NetworkManager: connectat.</li>
                <li>Connectivitat: completa.</li>
                <li>Wi-Fi: activat i disponible.</li>
                <li>WWAN: no disponible.</li>
            </ul>

            <h5>Interfícies de xarxa</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Interfície</th>
                            <th>Tipus</th>
                            <th>Estat</th>
                            <th>Configuració</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>wlp0s20u3u4</td>
                            <td>Wi-Fi</td>
                            <td>Activa</td>
                            <td>192.168.1.42/24</td>
                        </tr>
                        <tr>
                            <td>enp4s0f1</td>
                            <td>Ethernet</td>
                            <td>No disponible</td>
                            <td>Sense connexió</td>
                        </tr>
                        <tr>
                            <td>lo</td>
                            <td>Loopback</td>
                            <td>Activa</td>
                            <td>127.0.0.1/8 i ::1/128</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h5>Connexió actual</h5>
            <ul>
                <li>Perfil de connexió Wi-Fi: <code>jeff-broken</code>.</li>
                <li>Adreça IP: <code>192.168.1.42/24</code>.</li>
                <li>Passarel·la predeterminada: <code>192.168.1.1</code>.</li>
                <li>Xarxa local: <code>192.168.1.0/24</code>.</li>
                <li>Configuració IPv4 i IPv6 automàtica (DHCP/autoconfiguració).</li>
            </ul>

            <p class="text-muted small mb-0">
                L'adreça IP indicada correspon a la configuració observada
                durant l'inventari. No es considera una adreça estàtica.
            </p>

        </div>
    </div>

    <!-- DNS -->
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">4.2. Resolució DNS</h4>
        </div>
        <div class="card-body">

            <h5>Systemd-resolved</h5>
            <ul>
                <li>Servei de resolució DNS gestionat per <code>systemd-resolved</code>.</li>
                <li><code>/etc/resolv.conf</code> és un enllaç simbòlic a
                    <code>/run/systemd/resolve/stub-resolv.conf</code>.</li>
                <li>Resolució local mitjançant el servei stub de systemd-resolved.</li>
            </ul>

            <h5>Servidors DNS globals observats</h5>
            <ul>
                <li><code>193.110.81.0</code> — dns0.eu.</li>
                <li><code>2a0f:fc80::</code> — dns0.eu.</li>
                <li><code>185.253.5.0</code> — dns0.eu.</li>
                <li><code>2a0f:fc81::</code> — dns0.eu.</li>
            </ul>

            <h5>Servidors DNS associats a la connexió Wi-Fi</h5>
            <ul>
                <li><code>80.58.61.250</code>.</li>
                <li><code>80.58.61.254</code>.</li>
            </ul>

            <p>
                La connexió Wi-Fi utilitza DNS automàtic i no té servidors
                DNS configurats manualment al perfil de NetworkManager.
            </p>

            <p>
                Durant l'inventari s'han observat servidors DNS globals
                de dns0.eu i servidors DNS associats a la connexió Wi-Fi.
                Encara no s'ha identificat l'origen de la configuració
                global de dns0.eu.
            </p>

            <h5>Fitxers de configuració revisats</h5>
            <ul>
                <li><code>/etc/resolv.conf</code></li>
                <li><code>/etc/systemd/resolved.conf</code></li>
                <li><code>/etc/NetworkManager/conf.d/</code></li>
            </ul>

            <p class="text-muted small mb-0">
                No s'han trobat directives actives als dos últims
                elements revisats. Cal investigar l'origen de la
                configuració DNS global abans de reproduir-la en un
                altre ordinador.
            </p>

        </div>
    </div>

    <!-- FIREWALL -->
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">4.3. Tallafoc — firewalld</h4>
        </div>
        <div class="card-body">

            <ul>
                <li>Servei: <code>firewalld</code>.</li>
                <li>Estat: actiu i en execució.</li>
                <li>Zona predeterminada: <code>FedoraWorkstation</code>.</li>
                <li>Zona activa de la interfície Wi-Fi: <code>FedoraWorkstation</code>.</li>
                <li>Interfície associada: <code>wlp0s20u3u4</code>.</li>
            </ul>

            <h5>Serveis autoritzats</h5>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge text-bg-secondary">dhcpv6-client</span>
                <span class="badge text-bg-secondary">http</span>
                <span class="badge text-bg-secondary">https</span>
                <span class="badge text-bg-secondary">mdns</span>
                <span class="badge text-bg-secondary">mysql</span>
                <span class="badge text-bg-secondary">samba-client</span>
                <span class="badge text-bg-secondary">ssh</span>
            </div>

            <h5>Ports autoritzats</h5>
            <ul>
                <li><code>1025-65535/udp</code></li>
                <li><code>1025-65535/tcp</code></li>
            </ul>

            <h5>Altres opcions</h5>
            <ul>
                <li>Forwarding: activat (<code>yes</code>).</li>
                <li>Masquerade: desactivat (<code>no</code>).</li>
                <li>Regla específica: permet el servei MySQL des de
                    <code>10.1.1.0/24</code> mitjançant una regla rich rule IPv4.</li>
            </ul>

            <h5>Zones disponibles</h5>
            <p>
                FedoraServer, FedoraWorkstation, block, dmz, drop,
                external, home, internal, nm-shared, public, trusted
                i work.
            </p>

            <div class="alert alert-warning mb-0">
                <strong>Configuració pendent de revisió:</strong>
                l'ampli rang de ports TCP i UDP
                (<code>1025-65535</code>) i l'opció
                <code>forward=yes</code> mereixen una revisió de seguretat.
                Es documenten tal com estan configurats, sense modificar-los
                durant aquest inventari.
            </div>

        </div>
    </div>

    <!-- SELINUX -->
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">4.4. SELinux</h4>
        </div>
        <div class="card-body">

            <ul>
                <li>SELinux: habilitat.</li>
                <li>Política: <code>targeted</code>.</li>
                <li>Mode actual: <code>permissive</code>.</li>
                <li>Mode configurat al sistema: <code>permissive</code>.</li>
                <li>MLS: habilitat.</li>
                <li>Gestió d'elements desconeguts: <code>deny_unknown</code> permès.</li>
            </ul>

            <div class="alert alert-warning mb-0">
                SELinux està instal·lat i habilitat, però el mode
                permissive registra les violacions sense bloquejar
                les operacions. Cal tenir-ho present en una futura
                migració i revisar si es vol mantenir aquest comportament.
            </div>

        </div>
    </div>

    <!-- SSH -->
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">4.5. SSH i autenticació remota</h4>
        </div>
        <div class="card-body">

            <h5>Configuració SSH del client</h5>
            <p>Fitxer principal: <code>~/.ssh/config</code>.</p>

            <p>Àlies configurats:</p>
            <ul>
                <li>
                    <code>elliot-server</code>: connexió al servidor
                    remot amb l'usuari corresponent i una clau específica.
                </li>
                <li>
                    <code>github.com</code>: autenticació amb GitHub
                    mitjançant l'usuari SSH <code>git</code>.
                </li>
            </ul>

            <p>
                La configuració utilitza <code>IdentityFile</code>
                i <code>IdentitiesOnly yes</code>. Per a GitHub també
                hi ha configurat <code>AddKeysToAgent yes</code>.
            </p>

            <h5>Claus SSH identificades</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Tipus</th>
                            <th>Permisos observats</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>id_ed25519</td>
                            <td>Clau personal</td>
                            <td>600 / pública 644</td>
                        </tr>
                        <tr>
                            <td>deploy_elliot</td>
                            <td>Desplegament</td>
                            <td>600 / pública 644</td>
                        </tr>
                        <tr>
                            <td>deploy_api_elliot</td>
                            <td>Desplegament API</td>
                            <td>600 / pública 644</td>
                        </tr>
                        <tr>
                            <td>deploy_memoria</td>
                            <td>Desplegament</td>
                            <td>600 / pública 644</td>
                        </tr>
                        <tr>
                            <td>deploy_finguer</td>
                            <td>Desplegament</td>
                            <td>600 / pública 644</td>
                        </tr>
                        <tr>
                            <td>github_actions_api_finguer</td>
                            <td>GitHub Actions</td>
                            <td>600 / pública 644</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h5>Agent SSH i keychain</h5>
            <ul>
                <li>Agent SSH: utilitzat.</li>
                <li>Clau carregada durant la inspecció: <code>id_ed25519</code>.</li>
                <li>Keychain: versió 3.0.10.</li>
                <li>Clau identificada a keychain: <code>id_ed25519</code>.</li>
            </ul>

            <h5>Servidor SSH</h5>
            <ul>
                <li>Servei <code>sshd</code>: desactivat i inactiu.</li>
                <li>La màquina utilitza SSH com a client per connectar-se
                    a servidors remots i a GitHub.</li>
            </ul>

            <p>
                No és necessari tenir el servidor SSH activat per fer
                connexions SSH sortints.
            </p>

            <div class="alert alert-warning mb-0">
                <strong>Important per a les còpies de seguretat:</strong>
                les claus privades són necessàries per recuperar les
                connexions existents. Cal conservar-les en una còpia
                de seguretat xifrada i protegida. No s'han d'incloure
                en repositoris ni en aquesta documentació.
            </div>

        </div>
    </div>

    <!-- CERTIFICATS -->
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">4.6. Certificats digitals i mkcert</h4>
        </div>
        <div class="card-body">

            <h5>mkcert</h5>
            <ul>
                <li>Versió: <code>1.4.4</code>.</li>
                <li>Directori de la CA local:
                    <code>~/.local/share/mkcert</code>.</li>
                <li>CA arrel: <code>rootCA.pem</code>.</li>
                <li>Clau privada de la CA: <code>rootCA-key.pem</code>.</li>
                <li>La CA local està instal·lada com a autoritat de confiança
                    del sistema.</li>
                <li>Permisos de la clau privada de la CA: <code>400</code>.</li>
            </ul>

            <h5>Certificats locals de desenvolupament</h5>
            <p>Directori: <code>/etc/httpd/ssl/</code></p>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Domini</th>
                            <th>Fitxer de certificat</th>
                            <th>Caducitat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>elliot.local</td>
                            <td>elliot.local.pem</td>
                            <td>14/09/2028</td>
                        </tr>
                        <tr>
                            <td>api.elliot.local</td>
                            <td>api.elliot.local.pem</td>
                            <td>06/02/2029</td>
                        </tr>
                        <tr>
                            <td>hispantic.local</td>
                            <td>hispantic.local.pem</td>
                            <td>14/09/2028</td>
                        </tr>
                        <tr>
                            <td>finguer.local</td>
                            <td>finguer.local+2.pem</td>
                            <td>07/09/2028</td>
                        </tr>
                        <tr>
                            <td>memoriaterrassa.local</td>
                            <td>memoriaterrassa.local.pem</td>
                            <td>09/09/2028</td>
                        </tr>
                        <tr>
                            <td>api.finguer.local</td>
                            <td>api.finguer.local.pem</td>
                            <td>26/02/2029</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p>
                Els certificats anteriors estan referenciats en les
                configuracions SSL dels VirtualHosts d'Apache.
                Les claus privades corresponents es troben al mateix
                directori.
            </p>

            <h5>Permisos dels certificats</h5>
            <ul>
                <li>Certificats públics: permisos <code>644</code>.</li>
                <li>Claus privades dels certificats utilitzats:
                    permisos <code>600</code>.</li>
            </ul>

            <h5>Certbot</h5>
            <ul>
                <li>Versió: <code>5.10.0</code>.</li>
                <li>Temporitzador de renovació:
                    <code>certbot-renew.timer</code>.</li>
                <li>Estat del temporitzador: habilitat.</li>
                <li>Certificats registrats actualment: cap.</li>
            </ul>

            <p class="text-muted small mb-0">
                El certificat anterior de cehterrassa.cat s'ha eliminat
                perquè ja no és necessari. El temporitzador de Certbot
                es manté habilitat.
            </p>

        </div>
    </div>

    <!-- BACKUP -->
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">4.7. Fitxers i configuracions que cal conservar</h4>
        </div>
        <div class="card-body">

            <h5>Configuració de xarxa i DNS</h5>
            <ul>
                <li><code>/etc/NetworkManager/system-connections/</code></li>
                <li><code>/etc/systemd/resolved.conf</code></li>
                <li><code>/etc/NetworkManager/conf.d/</code></li>
                <li><code>/etc/resolv.conf</code> (recrear l'enllaç simbòlic
                    si és necessari, no copiar-lo com un fitxer ordinari).</li>
            </ul>

            <h5>Tallafoc i seguretat</h5>
            <ul>
                <li>Configuració permanent de <code>firewalld</code>,
                    especialment les zones i regles personalitzades.</li>
                <li>Configuració SELinux que es vulgui mantenir.</li>
            </ul>

            <h5>SSH</h5>
            <ul>
                <li><code>~/.ssh/config</code></li>
                <li><code>~/.ssh/known_hosts</code></li>
                <li>Totes les claus privades i públiques necessàries.</li>
                <li>Configuració de keychain, si escau.</li>
            </ul>

            <h5>Certificats</h5>
            <ul>
                <li><code>~/.local/share/mkcert/</code></li>
                <li><code>/etc/httpd/ssl/</code></li>
                <li>Configuracions SSL dels VirtualHosts d'Apache.</li>
            </ul>

            <div class="alert alert-danger mb-0">
                <strong>Seguretat:</strong> la còpia de
                <code>rootCA-key.pem</code> i de totes les claus privades
                SSH i SSL s'ha de fer de manera xifrada i amb accés restringit.
                No s'han de guardar en còpies públiques ni en repositoris Git.
            </div>

        </div>
    </div>

    <!-- RESTAURACIÓ -->
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">4.8. Restauració en un ordinador nou</h4>
        </div>
        <div class="card-body">

            <ol>
                <li>Instal·lar i configurar NetworkManager.</li>
                <li>Configurar les connexions de xarxa necessàries.</li>
                <li>Revisar i reproduir la configuració DNS, incloent-hi
                    l'origen dels servidors DNS globals.</li>
                <li>Instal·lar i configurar <code>firewalld</code>,
                    recuperant les zones, serveis i regles necessàries.</li>
                <li>Revisar el mode SELinux abans de restaurar la
                    configuració de seguretat.</li>
                <li>Instal·lar OpenSSH i recuperar la configuració del
                    client i les claus privades des de la còpia segura.</li>
                <li>Instal·lar mkcert i recuperar la CA local si es vol
                    mantenir la confiança dels certificats existents.</li>
                <li>Recuperar els certificats i les claus SSL dels
                    VirtualHosts d'Apache.</li>
                <li>Instal·lar Certbot només si torna a ser necessari
                    gestionar certificats públics.</li>
                <li>Verificar les connexions SSH, la resolució DNS,
                    els certificats HTTPS i les regles del tallafoc.</li>
            </ol>

            <p class="text-muted small mb-0">
                La configuració s'ha de restaurar de manera controlada,
                revisant els permisos de les claus privades i adaptant
                les interfícies i connexions al maquinari nou.
            </p>

        </div>
    </div>

    <!-- PENDENT -->
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">4.9. Observacions i tasques pendents</h4>
        </div>
        <div class="card-body">

            <ul>
                <li>Identificar l'origen de la configuració DNS global
                    de dns0.eu.</li>
                <li>Revisar el rang ampli de ports TCP i UDP autoritzats
                    al tallafoc.</li>
                <li>Revisar la necessitat de mantenir
                    <code>forward=yes</code>.</li>
                <li>Valorar si es vol mantenir SELinux en mode permissive
                    o passar a enforcing.</li>
                <li>Revisar periòdicament la caducitat dels certificats
                    locals mkcert.</li>
                <li>Comprovar que les còpies de seguretat inclouen les
                    claus SSH i la CA local de manera segura.</li>
            </ul>

        </div>
    </div>

</div>

<?php endif; ?>