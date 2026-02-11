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

    /* Intranet sticky justo debajo del header */
    #intranetNav {
        position: sticky;
        top: var(--headerH, 0px);
        z-index: 1040;
    }

    /* Empuja el contenido por debajo de header + intranet */
    body {
        padding-top: var(--topOffset, 0px);
    }
</style>

<script>
    function relocateIntranetNavOutsideContainer() {
        const intranetNav = document.getElementById("intranetNav");
        if (!intranetNav) return;

        const siteHeader = document.getElementById("siteHeader");
        if (!siteHeader) return;

        // Si está dentro de un .container, lo sacamos
        const container = intranetNav.closest(".container");
        if (container) siteHeader.insertAdjacentElement("afterend", intranetNav);
    }

    function setupTopOffsets() {
        const siteHeader = document.getElementById("siteHeader");
        const intranetNav = document.getElementById("intranetNav");
        if (!siteHeader) return;

        const apply = () => {
            const headerH = siteHeader.getBoundingClientRect().height;
            const intranetH = intranetNav ? intranetNav.getBoundingClientRect().height : 0;

            document.documentElement.style.setProperty("--headerH", `${headerH}px`);
            document.documentElement.style.setProperty("--topOffset", `${headerH + intranetH}px`);
        };

        // Varias pasadas para asegurar layout estable
        apply();
        requestAnimationFrame(apply);
        setTimeout(apply, 50);

        window.addEventListener("resize", apply);
        window.addEventListener("load", apply);

        if (window.ResizeObserver) {
            const ro = new ResizeObserver(apply);
            ro.observe(siteHeader);
            if (intranetNav) ro.observe(intranetNav);
        }

        // Bootstrap collapse (menú móvil)
        const navbarMenu = document.getElementById("navbarMenu");
        if (navbarMenu) {
            navbarMenu.addEventListener("shown.bs.collapse", () => requestAnimationFrame(apply));
            navbarMenu.addEventListener("hidden.bs.collapse", () => requestAnimationFrame(apply));
        }

        // Bootstrap offcanvas (tu menú "Més"): puede cambiar alturas si hay scrollbars
        const offcanvas = document.getElementById("menuOffcanvas");
        if (offcanvas) {
            offcanvas.addEventListener("shown.bs.offcanvas", () => requestAnimationFrame(apply));
            offcanvas.addEventListener("hidden.bs.offcanvas", () => requestAnimationFrame(apply));
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        relocateIntranetNavOutsideContainer();
        setupTopOffsets();
    });
</script>


<div class="container">