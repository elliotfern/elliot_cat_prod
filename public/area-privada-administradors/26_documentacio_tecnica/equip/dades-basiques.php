<?php

use App\Utils\Routes;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Documentació tècnica equip de treball</h1>
<h3>0. Dades bàsiques: Fedora 45 — ASUS N551JK</h3>
<?php if ($viewModel->isAdmin) : ?>


    <!-- Capçalera -->
    <header class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge text-bg-primary">Equip personal</span>
            <span class="badge text-bg-success">En funcionament</span>
        </div>

        <p class="text-body-secondary mb-0">
            Documentació tècnica del sistema operatiu, el maquinari,
            la configuració, el programari instal·lat, el manteniment
            i els procediments de recuperació de l'equip.
        </p>
    </header>

    <hr>

    <!-- Informació general -->
    <section class="mb-5" id="informacio-general">
        <h2 class="h4 mb-3">1. Informació general</h2>

        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <tbody>
                    <tr>
                        <th scope="row" class="w-25">Equip</th>
                        <td>ASUS N551JK</td>
                    </tr>
                    <tr>
                        <th scope="row">Sistema operatiu</th>
                        <td>Fedora Linux 45 Workstation Edition</td>
                    </tr>
                    <tr>
                        <th scope="row">Arquitectura</th>
                        <td>x86_64</td>
                    </tr>
                    <tr>
                        <th scope="row">Nucli (kernel)</th>
                        <td>7.2.15-300.fc45.x86_64</td>
                    </tr>
                    <tr>
                        <th scope="row">Entorn d'escriptori</th>
                        <td>XFCE</td>
                    </tr>
                    <tr>
                        <th scope="row">Servidor gràfic</th>
                        <td>X11</td>
                    </tr>
                    <tr>
                        <th scope="row">Nom de l'equip (hostname)</th>
                        <td>Elliot</td>
                    </tr>
                    <tr>
                        <th scope="row">Darrera revisió</th>
                        <td>07/11/2026</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Maquinari -->
    <section class="mb-5" id="maquinari">
        <h2 class="h4 mb-3">2. Maquinari</h2>

        <h3 class="h6">Processador</h3>
        <p>
            Intel Core i7-4710HQ @ 2,50 GHz.
            4 nuclis i 8 fils d'execució.
        </p>

        <h3 class="h6">Memòria</h3>
        <ul>
            <li>RAM: aproximadament 15 GiB.</li>
            <li>Memòria d'intercanvi (swap): 8 GiB mitjançant zram.</li>
        </ul>

        <h3 class="h6">Gràfics</h3>
        <ul>
            <li>Intel HD Graphics 4600 — controlador <code>i915</code>.</li>
            <li>NVIDIA GeForce GTX 850M — dispositiu detectat.</li>
        </ul>

        <h3 class="h6">Altres dispositius</h3>
        <ul>
            <li>Ethernet Realtek RTL8111/8168/8411.</li>
            <li>Lector de targetes Realtek RTL8411B.</li>
            <li>Àudio Intel.</li>
        </ul>
    </section>

    <!-- Emmagatzematge -->
    <section class="mb-5" id="emmagatzematge">
        <h2 class="h4 mb-3">3. Emmagatzematge</h2>

        <h3 class="h6">Unitat principal</h3>
        <ul>
            <li>SSD SanDisk SD7SB3Q256G1002.</li>
            <li>Capacitat comercial: 256 GB.</li>
            <li>Interfície SATA 3.2, 6 Gb/s.</li>
            <li>Firmware: X2150000.</li>
            <li>TRIM disponible.</li>
        </ul>

        <h3 class="h6">Particionament</h3>

        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Partició</th>
                        <th>Format</th>
                        <th>Punt de muntatge</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>/dev/sda1</td>
                        <td>FAT32</td>
                        <td>/boot/efi</td>
                    </tr>
                    <tr>
                        <td>/dev/sda2</td>
                        <td>ext4</td>
                        <td>/boot</td>
                    </tr>
                    <tr>
                        <td>/dev/sda3</td>
                        <td>Btrfs</td>
                        <td>/ i /home</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="h6">Sistema de fitxers Btrfs</h3>
        <ul>
            <li>Subvolum <code>root</code> per al sistema.</li>
            <li>Subvolum <code>home</code> per als usuaris.</li>
            <li>Subvolum niat <code>var/lib/machines</code>.</li>
            <li>Compressió: <code>zstd:1</code>.</li>
            <li>Dades: perfil <code>single</code>.</li>
            <li>Metadades: perfil <code>DUP</code>.</li>
            <li>No s'han trobat instantànies (snapshots) en la inspecció inicial.</li>
        </ul>

        <div class="alert alert-warning">
            <strong>Important:</strong>
            <code>/</code> i <code>/home</code> són subvolums
            independents, però comparteixen la mateixa partició física.
            Per tant, no constitueixen còpies de seguretat independents.
        </div>
    </section>

    <!-- Estat del disc -->
    <section class="mb-5" id="estat-disc">
        <h2 class="h4 mb-3">4. Estat de l'SSD</h2>

        <p>
            Darrera comprovació SMART:
            <span class="badge text-bg-success">PASSED</span>
        </p>

        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <tbody>
                    <tr>
                        <th>Hores de funcionament</th>
                        <td>22.224 h</td>
                    </tr>
                    <tr>
                        <th>Cicles d'encesa</th>
                        <td>10.134</td>
                    </tr>
                    <tr>
                        <th>Sectors reassignats</th>
                        <td>13</td>
                    </tr>
                    <tr>
                        <th>Errors no corregibles notificats</th>
                        <td>297</td>
                    </tr>
                    <tr>
                        <th>Errors CRC SATA</th>
                        <td>0</td>
                    </tr>
                    <tr>
                        <th>Temperatura</th>
                        <td>44 °C</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-body-secondary small">
            El resultat general SMART és satisfactori, però hi ha
            comptadors històrics que caldrà controlar.
            Està pendent executar una prova SMART ampliada.
        </p>
    </section>



<?php endif; ?>