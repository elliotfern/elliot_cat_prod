<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">
            <h1>Agenda esdeveniments</h1>
            <h2>Llistat esdeveniments</h2>

            <div id="isAdminButton" style="display: none;">
                <?php if (isUserAdmin()) : ?>
                    <p>
                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['agenda']; ?>/nou-esdeveniment'" class="button btn-gran btn-secondari">Nou esdeveniment</button>

                    </p>
                <?php endif; ?>
            </div>

            <div id="agenda-llistat" class="agenda-llistat">
                <!-- Aquí se inyectará la agenda por meses -->
            </div>

        </div>
    </main>
</div>

<style>
    /* Contenedor principal */
    #agenda-wrapper {
        max-width: 960px;
        margin: 0 auto;
        padding: 1.5rem 1rem 3rem;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        color: #1f2933;
    }

    .agenda-event-meta {
        font-size: 0.9rem;
        font-weight: 600;
        /* más destacado */
        color: #111827;
        margin-top: 0.35rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: flex-start;
    }

    .agenda-event-meta strong {
        font-weight: 700;
    }

    /* Botón Modificar */
    .agenda-btn-modificar {
        font-size: 0.8rem;
        padding: 0.3rem 0.75rem;
        border-radius: 999px;
        border: 1px solid rgba(37, 99, 235, 0.7);
        background: rgba(59, 130, 246, 0.05);
        color: #1d4ed8;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        cursor: pointer;
        transition: background 0.15s ease, transform 0.1s ease, box-shadow 0.15s ease;
    }

    .agenda-btn-modificar:hover {
        background: rgba(59, 130, 246, 0.12);
        box-shadow: 0 3px 8px rgba(37, 99, 235, 0.25);
        transform: translateY(-1px);
    }

    .agenda-btn-modificar:active {
        transform: translateY(0);
        box-shadow: none;
    }

    /* Nuevo tipo: viatge */
    .agenda-badge-tipus-viatge {
        background: rgba(14, 165, 233, 0.08);
        color: #0369a1;
        border-color: rgba(14, 165, 233, 0.4);
    }


    .agenda-title {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    /* Bloque por mes */
    .agenda-month {
        background: #ffffff;
        border-radius: 12px;
        padding: 1.25rem 1.25rem 1rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(148, 163, 184, 0.3);
    }

    .agenda-month-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: #111827;
    }

    /* Lista de eventos */
    .agenda-event-list {
        list-style: none;
        margin: 0;
        padding: 0;
        border-top: 1px solid rgba(229, 231, 235, 0.9);
    }

    .agenda-event-item {
        padding: 0.75rem 0;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        border-bottom: 1px solid rgba(229, 231, 235, 0.7);
    }

    .agenda-event-item:last-child {
        border-bottom: none;
    }

    /* Contenido principal del evento */
    .agenda-event-main {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }

    .agenda-event-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
    }

    .agenda-event-meta {
        font-size: 0.85rem;
        color: #6b7280;
    }

    /* Badges */
    .agenda-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 0.25rem;
    }

    .agenda-badge {
        font-size: 0.75rem;
        padding: 0.2rem 0.5rem;
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

    /* Responsive: en pantallas medianas/grandes, alinea fecha y contenido */
    @media (min-width: 640px) {
        .agenda-event-item {
            flex-direction: row;
            justify-content: space-between;
            align-items: flex-start;
        }

        .agenda-event-main {
            flex: 1;
        }

        .agenda-event-meta {
            min-width: 150px;
            text-align: right;
        }
    }

    /* Pequeños detalles al pasar el ratón */
    .agenda-event-item:hover {
        background: rgba(249, 250, 251, 0.8);
    }
</style>