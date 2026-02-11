<header id="siteHeader" class="bg-white border-bottom">
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
    function setupIntranetNavOffset() {
        const intranetNav = document.getElementById("intranetNav");
        const siteHeader = document.getElementById("siteHeader");
        if (!intranetNav || !siteHeader) return;

        const EXTRA_PX = 2;

        const apply = () => {
            const rect = siteHeader.getBoundingClientRect();
            const headerH = rect.height;
            intranetNav.style.top = `calc(${headerH}px + ${EXTRA_PX}px)`;
        };

        apply();
        window.addEventListener("resize", apply);
        window.addEventListener("load", apply);

        if (window.ResizeObserver) {
            const ro = new ResizeObserver(() => apply());
            ro.observe(siteHeader);
        }

        // Bootstrap collapse events (abre/cierra el menú)
        const navbarMenu = document.getElementById("navbarMenu");
        if (navbarMenu) {
            navbarMenu.addEventListener("shown.bs.collapse", apply);
            navbarMenu.addEventListener("hidden.bs.collapse", apply);
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        setupIntranetNavOffset();
    });
</script>


<div class="container">