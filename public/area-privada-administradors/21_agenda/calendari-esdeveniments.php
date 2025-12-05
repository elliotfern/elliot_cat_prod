<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">
            <h1>Agenda esdeveniments</h1>
            <h2>Calendari esdeveniments</h2>

            <div id="isAdminButton" style="display: none;">
                <?php if (isUserAdmin()) : ?>
                    <p>
                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['agenda']; ?>/nou-esdeveniment'" class="button btn-gran btn-secondari">Nou esdeveniment</button>

                    </p>
                <?php endif; ?>
            </div>

            <section id="agenda-cal-wrapper">
                <div class="agenda-cal-header">
                    <button id="cal-prev" class="cal-nav-btn" type="button">«</button>
                    <h1 id="cal-month-title" class="cal-month-title">Agenda</h1>
                    <button id="cal-next" class="cal-nav-btn" type="button">»</button>
                </div>

                <div class="cal-weekdays">
                    <div class="cal-weekday">Dl</div>
                    <div class="cal-weekday">Dt</div>
                    <div class="cal-weekday">Dc</div>
                    <div class="cal-weekday">Dj</div>
                    <div class="cal-weekday">Dv</div>
                    <div class="cal-weekday">Ds</div>
                    <div class="cal-weekday">Dg</div>
                </div>

                <div id="cal-grid" class="cal-grid">
                    <!-- Aquí se pintan los días del mes -->
                </div>
            </section>

        </div>
    </main>
</div>

<style>
    .cal-day-event a {
        color: inherit;
        text-decoration: none;
        display: block;
    }

    .cal-day-event a:hover {
        text-decoration: underline;
    }

    #agenda-cal-wrapper {
        max-width: 960px;
        margin: 0 auto;
        padding: 1.5rem 1rem 3rem;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        color: #111827;
    }

    /* Cabecera con mes y navegación */
    .agenda-cal-header {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .cal-month-title {
        font-size: 1.4rem;
        font-weight: 700;
    }

    .cal-nav-btn {
        border: 1px solid rgba(148, 163, 184, 0.8);
        background: #ffffff;
        border-radius: 999px;
        padding: 0.25rem 0.7rem;
        cursor: pointer;
        font-size: 0.9rem;
        line-height: 1;
        transition: background 0.15s ease, transform 0.1s ease, box-shadow 0.15s ease;
    }

    .cal-nav-btn:hover {
        background: rgba(249, 250, 251, 1);
        box-shadow: 0 3px 10px rgba(148, 163, 184, 0.4);
        transform: translateY(-1px);
    }

    .cal-nav-btn:active {
        transform: translateY(0);
        box-shadow: none;
    }

    /* Cabecera días de la semana */
    .cal-weekdays {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        text-align: center;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6b7280;
        margin-bottom: 0.35rem;
    }

    .cal-weekday {
        padding: 0.35rem 0;
    }

    /* Grid de días */
    .cal-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 0.35rem;
    }

    /* Celda de día */
    .cal-day {
        min-height: 80px;
        background: #ffffff;
        border-radius: 10px;
        border: 1px solid rgba(229, 231, 235, 0.9);
        padding: 0.35rem 0.35rem 0.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        font-size: 0.78rem;
        overflow: hidden;
    }

    .cal-day--empty {
        background: transparent;
        border: none;
    }

    /* Cabecera de la celda (número de día) */
    .cal-day-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cal-day-number {
        font-weight: 600;
        font-size: 0.8rem;
    }

    .cal-day-date-pill {
        font-size: 0.65rem;
        padding: 0.05rem 0.4rem;
        border-radius: 999px;
        background: rgba(243, 244, 246, 0.9);
        color: #6b7280;
    }

    /* Hoy */
    .cal-day--today {
        border-color: rgba(59, 130, 246, 0.9);
        box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.5);
    }

    /* Lista de eventos dentro del día */
    .cal-day-events {
        margin-top: 0.1rem;
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    /* Evento */
    .cal-day-event {
        border-radius: 999px;
        padding: 0.05rem 0.35rem;
        font-size: 0.65rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Colores por tipus */
    .cal-day-event--reunio {
        background: rgba(59, 130, 246, 0.1);
        color: #1d4ed8;
    }

    .cal-day-event--visita_medica {
        background: rgba(236, 72, 153, 0.1);
        color: #be185d;
    }

    .cal-day-event--videotrucada {
        background: rgba(16, 185, 129, 0.1);
        color: #047857;
    }

    .cal-day-event--viatge {
        background: rgba(14, 165, 233, 0.1);
        color: #0369a1;
    }

    .cal-day-event--altre {
        background: rgba(107, 114, 128, 0.1);
        color: #374151;
    }

    /* Indicador cuando hay más eventos */
    .cal-day-more {
        font-size: 0.6rem;
        color: #6b7280;
    }

    /* Responsive */
    @media (max-width: 640px) {
        #agenda-cal-wrapper {
            padding: 1rem 0.5rem 2rem;
        }

        .cal-day {
            min-height: 72px;
            padding: 0.3rem 0.25rem;
        }

        .cal-month-title {
            font-size: 1.2rem;
        }
    }
</style>