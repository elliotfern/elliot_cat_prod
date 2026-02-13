<div class="barraNavegacio">
</div>

<div class="d-flex align-items-start justify-content-between mb-3">
    <div class="d-flex flex-column">
        <h2 class="mb-1">Gestor de projectes</h2>
        <h4 class="mb-0">
            <div id="titolForm"></div>
        </h4>
    </div>

    <button class="btn btn-outline-secondary" type="button" id="btnBack">
        ← Torna
    </button>
</div>

<!-- Alerts -->

<div class="alert alert-success" id="okMessage" style="display:none">
    <div id="okText"></div>
</div>
<div class="alert alert-danger" id="errMessage" style="display:none">
    <div id="errText"></div>
</div>

<form id="taskForm" class="form">
    <input type="hidden" name="id" id="id">

    <div class="row g-3">

        <!-- Projecte -->
        <div class="col-12 col-lg-6">
            <label for="project_id" class="form-label">Projecte</label>
            <select id="project_id" name="project_id" class="form-select">
                <option value="">— Sense projecte —</option>
            </select>
        </div>

        <!-- Data planificada -->
        <div class="col-12 col-lg-3">
            <label for="planned_date" class="form-label">Data planificada</label>
            <input id="planned_date" name="planned_date" type="date" class="form-control">
        </div>

        <!-- Estimació -->
        <div class="col-12 col-lg-3">
            <label for="estimated_hours" class="form-label">Estimació (hores)</label>
            <input id="estimated_hours" name="estimated_hours" type="number" class="form-control"
                min="0" step="0.25" placeholder="p. ex. 2.5">
        </div>

        <!-- Status -->
        <div class="col-12 col-lg-3">
            <label for="status" class="form-label">Estat</label>
            <select id="status" name="status" class="form-select" required>
                <option value="1">Backlog</option>
                <option value="2">En curs</option>
                <option value="3">Bloquejada</option>
                <option value="4">Feta</option>
            </select>
        </div>

        <!-- Prioritat -->
        <div class="col-12 col-lg-3">
            <label for="priority" class="form-label">Prioritat</label>
            <select id="priority" name="priority" class="form-select" required>
                <option value="1">1 (Baixa)</option>
                <option value="2">2</option>
                <option value="3" selected>3</option>
                <option value="4">4 (Alta)</option>
            </select>
        </div>

        <!-- Next -->
        <div class="col-12 col-lg-6 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="is_next" name="is_next">
                <label class="form-check-label" for="is_next">
                    Marcar com a “Next”
                </label>
                <div class="form-text">Si ho actives, aquesta tasca es pot destacar com la següent prioritària.</div>
            </div>
        </div>

        <!-- Títol -->
        <div class="col-12">
            <label for="title" class="form-label">Títol</label>
            <input id="title" name="title" type="text" class="form-control" maxlength="220" required>
        </div>

        <!-- Subject -->
        <div class="col-12">
            <label for="subject" class="form-label">Assumpte (opcional)</label>
            <input id="subject" name="subject" type="text" class="form-control" maxlength="220">
        </div>

        <!-- Blocked reason (només si status=3) -->
        <div class="col-12 d-none" id="blockedWrap">
            <label for="blocked_reason" class="form-label">Motiu de bloqueig</label>
            <input id="blocked_reason" name="blocked_reason" type="text" class="form-control" maxlength="255">
        </div>

        <!-- Notes -->
        <div class="col-12">
            <label for="notes" class="form-label">Notes</label>
            <textarea id="notes" name="notes" class="form-control" rows="6"
                placeholder="Notes, passos, enllaços..."></textarea>
        </div>

    </div>

    <div class="mt-4 d-flex justify-content-end">
        <button id="btnProjecte" class="btn btn-primary" type="submit">Desar</button>
    </div>
</form>