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

<div class="container">

    <style>
        /* Header por encima */
        #siteHeader {
            z-index: 1050;
        }

        /* Intranet siempre por debajo (si es sticky o fixed) */
        #intranetNav {
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

            const container = intranetNav.closest(".container");
            if (!container) return;

            const siteHeader = document.getElementById("siteHeader");
            if (!siteHeader) return;

            // moverlo justo después del header
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

                // intranet siempre debajo del header
                if (intranetNav) {
                    intranetNav.style.top = `calc(${headerH}px + ${EXTRA_PX}px)`;
                }

                // empujar contenido: header + intranet
                const total = headerH + intranetH + EXTRA_PX;
                document.documentElement.style.setProperty("--topOffset", `${total}px`);
            };

            // 1) aplica ya
            apply();

            // 2) eventos típicos
            window.addEventListener("resize", apply);
            window.addEventListener("load", apply);

            // 3) si cambia tamaño (menú móvil abierto/cerrado, etc.)
            if (window.ResizeObserver) {
                const ro = new ResizeObserver(() => apply());
                ro.observe(siteHeader);
                if (intranetNav) ro.observe(intranetNav);
            }

            // 4) Bootstrap collapse (abre/cierra navbar móvil)
            const navbarMenu = document.getElementById("navbarMenu");
            if (navbarMenu) {
                navbarMenu.addEventListener("shown.bs.collapse", apply);
                navbarMenu.addEventListener("hidden.bs.collapse", apply);
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            // IMPORTANTE: primero mover, luego medir/offsets
            relocateIntranetNavOutsideContainer();

            // espera 1 frame para que el layout “asiente”
            requestAnimationFrame(() => {
                setupTopOffsets();
            });
        });
    </script>