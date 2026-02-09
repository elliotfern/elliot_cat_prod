<header id="siteHeader" class="header">
    <div class="headerContent">
        <!-- Logo -->
        <h1 class="logo">
            <a href="/ca/homepage" class="text-decoration-none text-dark">Elliot Fernandez</a>
        </h1>

        <!-- Toggle Menu Button -->
        <button class="toggleMenuButton" id="menuToggle">☰</button>

        <!-- Navigation Menu -->
        <nav class="containerMenu menuHidden" id="navbarMenu">
            <ul>
                <li><a href="/ca/homepage">Inici</a></li>
                <li><a href="/about">Sobre l'autor</a></li>
                <li><a href="/biblioteca">Biblioteca</a></li>
                <li><a href="/ca/historia">Història</a></li>
                <li><a href="/blog">Blog</a></li>

                <!-- Languages Dropdown -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" id="languagesDropdown">Languages</a>
                    <ul class="superMenu1" style="display:none">
                        <li><a href="#">English</a></li>
                        <li><a href="#">Spanish</a></li>
                        <li><a href="#">Italian</a></li>
                        <li><a href="#">French</a></li>
                        <li><a href="#">Catalan</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</header>

<script>
    function setupIntranetNavOffset() {
        const intranetNav = document.getElementById("intranetNav");
        const siteHeader = document.getElementById("siteHeader");

        if (!intranetNav || !siteHeader) return;

        const EXTRA_PX = 2; // ajustable: 1..4 si quieres

        const apply = () => {
            // getBoundingClientRect da altura real en px (puede ser decimal)
            const rect = siteHeader.getBoundingClientRect();
            const headerH = rect.height;

            // Evita redondeos; sumamos extra para que nunca se “meta debajo”
            intranetNav.style.top = `calc(${headerH}px + ${EXTRA_PX}px)`;
        };

        apply();

        // Recalcula al redimensionar
        window.addEventListener("resize", apply);

        // Recalcula cuando el header cambie de tamaño (menú visible/hidden, fonts, etc.)
        if (window.ResizeObserver) {
            const ro = new ResizeObserver(() => apply());
            ro.observe(siteHeader);
        }

        // Por si cambian fuentes/estilos después de DOMContentLoaded
        window.addEventListener("load", apply);

        // También recalcula al abrir/cerrar tu menú superior
        const menuToggle = document.getElementById("menuToggle");
        if (menuToggle) {
            menuToggle.addEventListener("click", () => {
                // espera a que cambien clases y layout
                requestAnimationFrame(() => apply());
            });
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const languagesDropdown = document.getElementById("languagesDropdown");
        const languageMenu = document.querySelector(".superMenu1");

        setupIntranetNavOffset();

        if (languagesDropdown && languageMenu) {
            languagesDropdown.addEventListener("click", function(event) {
                event.preventDefault();
                languageMenu.style.display = languageMenu.style.display === "flex" ? "none" : "flex";
            });

            document.addEventListener("click", function(event) {
                if (!languagesDropdown.contains(event.target) && !languageMenu.contains(event.target)) {
                    languageMenu.style.display = "none";
                }
            });
        }
    });
</script>

<div class="container">