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
    document.getElementById("menuToggle").addEventListener("click", function() {
        let menu = document.getElementById("navbarMenu");
        if (menu.classList.contains("menuHidden")) {
            menu.classList.remove("menuHidden");
            menu.classList.add("menuVisible");
        } else {
            menu.classList.remove("menuVisible");
            menu.classList.add("menuHidden");
        }
    });

    export function setupIntranetNavOffset() {
        const intranetNav = document.getElementById("intranetNav");
        if (!intranetNav) return;

        // Intenta detectar el header superior fijo
        const siteHeader =
            (document.getElementById("siteHeader")) ||
            (document.querySelector("header")); // fallback razonable

        const apply = () => {
            const headerH = siteHeader ? siteHeader.getBoundingClientRect().height : 0;
            intranetNav.style.top = `${Math.max(0, Math.round(headerH))}px`;
        };

        apply();
        window.addEventListener("resize", apply);
    }

    document.addEventListener("DOMContentLoaded", function() {
        const languagesDropdown = document.getElementById("languagesDropdown");
        const languageMenu = document.querySelector(".superMenu1");

        setupIntranetNavOffset();

        languagesDropdown.addEventListener("click", function(event) {
            event.preventDefault();
            languageMenu.style.display = languageMenu.style.display === "flex" ? "none" : "flex";
        });

        // Cerrar el menú si se hace clic fuera de él
        document.addEventListener("click", function(event) {
            if (!languagesDropdown.contains(event.target) && !languageMenu.contains(event.target)) {
                languageMenu.style.display = "none";
            }
        });
    });
</script>

<div class="container">