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
        #siteHeader {
            z-index: 1050;
        }

        /* Intranet fijo debajo del header */
        #intranetNav {
            position: fixed;
            left: 0;
            right: 0;
            top: var(--headerH, 0px);
            z-index: 1040;
        }

        /* El contenido baja header + intranet */
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

            siteHeader.insertAdjacentElement("afterend", intranetNav);
        }

        function setupTopOffsets() {
            const siteHeader = document.getElementById("siteHeader");
            const intranetNav = document.getElementById("intranetNav");
            if (!siteHeader) return;

            const apply = () => {
                const headerH = siteHeader.getBoundingClientRect().height;
                const intranetH = intranetNav ? intranetNav.getBoundingClientRect().height : 0;

                // 1) intranet pegado al header
                document.documentElement.style.setProperty("--headerH", `${headerH}px`);

                // 2) contenido por debajo de ambos
                document.documentElement.style.setProperty("--topOffset", `${headerH + intranetH}px`);
            };

            apply();
            window.addEventListener("resize", apply);
            window.addEventListener("load", apply);

            if (window.ResizeObserver) {
                const ro = new ResizeObserver(apply);
                ro.observe(siteHeader);
                if (intranetNav) ro.observe(intranetNav);
            }

            // Bootstrap: al abrir/cerrar el menú móvil cambia la altura del header
            const navbarMenu = document.getElementById("navbarMenu");
            if (navbarMenu) {
                navbarMenu.addEventListener("shown.bs.collapse", apply);
                navbarMenu.addEventListener("hidden.bs.collapse", apply);
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            relocateIntranetNavOutsideContainer();
            requestAnimationFrame(setupTopOffsets);
        });
    </script>