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

<script>
    function relocateIntranetNavOutsideContainer() {
        const intranetNav = document.getElementById("intranetNav");
        if (!intranetNav) return;

        const container = intranetNav.closest(".container");
        if (!container) return;

        const siteHeader = document.getElementById("siteHeader");
        if (!siteHeader) return;

        // Mueve el intranetNav justo después del header (fuera del container)
        siteHeader.insertAdjacentElement("afterend", intranetNav);
    }

    function setupTopOffsets() {
        const siteHeader = document.getElementById("siteHeader");
        const intranetNav = document.getElementById("intranetNav");
        if (!siteHeader) return;

        const EXTRA_PX = 2;

        const apply = () => {
            const headerH = siteHeader.getBoundingClientRect().height;
            const intranetH = intranetNav ? intranetNav.getBoundingClientRect().height : 0;

            // 1) colocar intranetNav justo debajo del header
            if (intranetNav) {
                intranetNav.style.top = `calc(${headerH}px + ${EXTRA_PX}px)`;
            }

            // 2) empujar el contenido: header + intranet
            const totalOffset = headerH + intranetH + EXTRA_PX;
            document.documentElement.style.setProperty("--topOffset", `${totalOffset}px`);
        };

        apply();
        window.addEventListener("resize", apply);
        window.addEventListener("load", apply);

        if (window.ResizeObserver) {
            const ro = new ResizeObserver(() => apply());
            ro.observe(siteHeader);
            if (intranetNav) ro.observe(intranetNav);
        }

        // Bootstrap collapse (cuando abre/cierra el menú cambia altura del header)
        const navbarMenu = document.getElementById("navbarMenu");
        if (navbarMenu) {
            navbarMenu.addEventListener("shown.bs.collapse", apply);
            navbarMenu.addEventListener("hidden.bs.collapse", apply);
        }
    }

    document.addEventListener("DOMContentLoaded", setupTopOffsets);

    document.addEventListener("DOMContentLoaded", function() {
        relocateIntranetNavOutsideContainer();
    });
</script>

<div class="container">

    <style>
        /* Header por encima de todo */
        #siteHeader {
            z-index: 1050;
            /* similar al navbar en bootstrap */
        }

        /* Intranet nav justo debajo del header, siempre */
        #intranetNav {
            position: sticky;
            /* o fixed si lo usas así */
            z-index: 1040;
            /* un pelín por debajo del header */
        }

        /* Como el header es fixed-top, hay que empujar el contenido hacia abajo */
        body {
            padding-top: var(--siteHeaderH, 0px);
        }
    </style>