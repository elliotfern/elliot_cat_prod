<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">
            <h1>Agenda esdeveniments</h1>

            <div id="isAdminButton" style="display: none;">
                <?php if (isUserAdmin()) : ?>
                    <p>
                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['agenda']; ?>/nou-esdeveniment'" class="button btn-gran btn-secondari">Nou esdeveniment</button>

                    </p>
                <?php endif; ?>
            </div>

            <section
                id="agenda-esdeveniment-wrapper"
                data-esdeveniment-id="">
                <div class="agenda-esdeveniment-header">
                    <a href="/gestio/agenda" class="btn-secundari-agenda">
                        ← Tornar a l'agenda
                    </a>
                    <a
                        id="btn-modificar-esdeveniment"
                        class="btn-principal-agenda"
                        href="#">
                        Modificar
                    </a>
                </div>

                <div id="agenda-esdeveniment-main" class="agenda-esdeveniment-main">
                    <p>Carregant esdeveniment...</p>
                </div>
            </section>


        </div>
    </main>
</div>

<style>
    #agenda-esdeveniment-wrapper {
        max-width: 960px;
        margin: 0 auto;
        padding: 1.5rem 1rem 3rem;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        color: #111827;
    }

    .agenda-esdeveniment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    /* Botones */
    .btn-principal-agenda,
    .btn-secundari-agenda {
        border-radius: 999px;
        padding: 0.4rem 1rem;
        font-size: 0.85rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        cursor: pointer;
        transition: background 0.15s ease, box-shadow 0.15s ease, transform 0.1s ease;
    }

    .btn-principal-agenda {
        border: 1px solid rgba(37, 99, 235, 0.9);
        background: rgba(59, 130, 246, 0.1);
        color: #1d4ed8;
    }

    .btn-principal-agenda:hover {
        background: rgba(59, 130, 246, 0.2);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        transform: translateY(-1px);
    }

    .btn-secundari-agenda {
        border: 1px solid rgba(148, 163, 184, 0.9);
        background: #ffffff;
        color: #4b5563;
    }

    .btn-secundari-agenda:hover {
        background: rgba(249, 250, 251, 1);
        box-shadow: 0 3px 10px rgba(148, 163, 184, 0.4);
        transform: translateY(-1px);
    }

    /* Tarjeta principal del evento */
    .agenda-esdeveniment-main {
        background: #ffffff;
        border-radius: 16px;
        padding: 1.5rem 1.25rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
        border: 1px solid rgba(226, 232, 240, 1);
    }

    /* Título */
    .agenda-detall-titol {
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
        color: #111827;
    }

    /* Fecha principal grande */
    .agenda-detall-data {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2933;
        margin-bottom: 0.35rem;
    }

    /* Subtexto de fecha/hora */
    .agenda-detall-data-sub {
        font-size: 0.85rem;
        color: #6b7280;
        margin-bottom: 0.75rem;
    }

    /* Badges de tipus/estat reutilizando estilos previos */
    .agenda-detall-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-bottom: 1rem;
    }

    .agenda-badge {
        font-size: 0.75rem;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
    }

    /* Tipus */
    .agenda-badge-tipus-reunio {
        background: rgba(59, 130, 246, 0.08);
        color: #1d4ed8;
        border-color: rgba(59, 130, 246, 0.35);
    }

    .agenda-badge-tipus-visita_medica {
        background: rgba(236, 72, 153, 0.08);
        color: #be185d;
        border-color: rgba(236, 72, 153, 0.35);
    }

    .agenda-badge-tipus-videotrucada {
        background: rgba(16, 185, 129, 0.08);
        color: #047857;
        border-color: rgba(16, 185, 129, 0.35);
    }

    .agenda-badge-tipus-viatge {
        background: rgba(14, 165, 233, 0.08);
        color: #0369a1;
        border-color: rgba(14, 165, 233, 0.4);
    }

    .agenda-badge-tipus-altre {
        background: rgba(107, 114, 128, 0.06);
        color: #374151;
        border-color: rgba(107, 114, 128, 0.35);
    }

    /* Estat */
    .agenda-badge-estat-pendent {
        background: rgba(234, 179, 8, 0.1);
        color: #92400e;
        border-color: rgba(234, 179, 8, 0.5);
    }

    .agenda-badge-estat-confirmat {
        background: rgba(34, 197, 94, 0.08);
        color: #166534;
        border-color: rgba(34, 197, 94, 0.45);
    }

    .agenda-badge-estat-cancel·lat,
    .agenda-badge-estat-cancel-lat {
        background: rgba(239, 68, 68, 0.08);
        color: #b91c1c;
        border-color: rgba(239, 68, 68, 0.45);
    }

    /* Bloques de información */
    .agenda-detall-section {
        margin-bottom: 1rem;
    }

    .agenda-detall-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #9ca3af;
        margin-bottom: 0.2rem;
    }

    .agenda-detall-value {
        font-size: 0.9rem;
        color: #374151;
    }

    .agenda-detall-value a {
        color: #2563eb;
        text-decoration: none;
    }

    .agenda-detall-value a:hover {
        text-decoration: underline;
    }

    /* Descripción */
    .agenda-detall-descripcio {
        font-size: 0.9rem;
        color: #4b5563;
        line-height: 1.5;
    }

    /* Meta de creación/actualización */
    .agenda-detall-meta {
        margin-top: 1rem;
        font-size: 0.8rem;
        color: #9ca3af;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    /* Responsive */
    @media (max-width: 640px) {
        #agenda-esdeveniment-wrapper {
            padding: 1rem 0.5rem 2rem;
        }

        .agenda-esdeveniment-header {
            flex-direction: column;
            align-items: stretch;
        }

        .agenda-esdeveniment-main {
            padding: 1.2rem 1rem;
        }

        .agenda-detall-titol {
            font-size: 1.2rem;
        }
    }
</style>