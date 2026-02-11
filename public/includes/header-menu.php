<header id="siteHeader" class="bg-white border-bottom fixed-top">
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">

            <!-- Logo / Brand -->
            <a class="navbar-brand fw-bold text-dark text-decoration-none" href="/ca/homepage">
                Elliot Fernandez
            </a>

            <!-- Toggler (mobile) -->
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarMenu"
                aria-controls="navbarMenu"
                aria-expanded="false"
                aria-label="Toggle navigation"
                id="menuToggle">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menu -->
            <div class="collapse navbar-collapse" id="navbarMenu">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

                    <li class="nav-item">
                        <a class="nav-link" href="/ca/homepage">Inici</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about">Sobre l'autor</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/biblioteca">Biblioteca</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ca/historia">Història</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/blog">Blog</a>
                    </li>

                    <!-- Languages dropdown -->
                    <li class="nav-item dropdown">
                        <a
                            class="nav-link dropdown-toggle"
                            href="#"
                            id="languagesDropdown"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                            Languages
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languagesDropdown">
                            <li><a class="dropdown-item" href="#">English</a></li>
                            <li><a class="dropdown-item" href="#">Spanish</a></li>
                            <li><a class="dropdown-item" href="#">Italian</a></li>
                            <li><a class="dropdown-item" href="#">French</a></li>
                            <li><a class="dropdown-item" href="#">Catalan</a></li>
                        </ul>
                    </li>

                </ul>
            </div>
        </div>
    </nav>
</header>

<style>
    #siteHeader {
        z-index: 1050;
    }

    #intranetNav {
        position: sticky;
        top: var(--headerH, 0px);
        z-index: 1040;
    }

    /* SOLO compensamos el header fijo */
    body {
        padding-top: var(--headerH, 0px);
    }
</style>

<script>
    function relocateIntranetNavOutsideContainer() {
        const intranetNav = document.getElementById("intranetNav");
        if (!intranetNav) return;

        const siteHeader = document.getElementById("siteHeader");
        if (!siteHeader) return;

        const container = intranetNav.closest(".container");
        if (container) siteHeader.insertAdjacentElement("afterend", intranetNav);
    }

    function setupTopLayout() {
        const siteHeader = document.getElementById("siteHeader");
        const intranetNav = document.getElementById("intranetNav");
        if (!siteHeader) return;

        // Tu contenedor global (el que abres para no repetir código)
        const mainContainer = document.querySelector("div.container");

        const apply = () => {
            const headerH = siteHeader.getBoundingClientRect().height;
            const intranetH = intranetNav ? intranetNav.getBoundingClientRect().height : 0;

            // Header fijo: empuja el body solo con el header
            document.documentElement.style.setProperty("--headerH", `${headerH}px`);

            // Empuja el contenido con la altura del intranet (NO el body)
            if (mainContainer) {
                mainContainer.style.paddingTop = intranetNav ? `${intranetH}px` : "0px";
            }
        };

        apply();
        requestAnimationFrame(apply);
        setTimeout(apply, 50);

        window.addEventListener("resize", apply);
        window.addEventListener("load", apply);

        if (window.ResizeObserver) {
            const ro = new ResizeObserver(apply);
            ro.observe(siteHeader);
            if (intranetNav) ro.observe(intranetNav);
            if (mainContainer) ro.observe(mainContainer);
        }

        const navbarMenu = document.getElementById("navbarMenu");
        if (navbarMenu) {
            navbarMenu.addEventListener("shown.bs.collapse", () => requestAnimationFrame(apply));
            navbarMenu.addEventListener("hidden.bs.collapse", () => requestAnimationFrame(apply));
        }

        const offcanvas = document.getElementById("menuOffcanvas");
        if (offcanvas) {
            offcanvas.addEventListener("shown.bs.offcanvas", () => requestAnimationFrame(apply));
            offcanvas.addEventListener("hidden.bs.offcanvas", () => requestAnimationFrame(apply));
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        relocateIntranetNavOutsideContainer();
        setupTopLayout();
    });
</script>

<div class="container">