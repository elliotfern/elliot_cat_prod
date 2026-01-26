<div class="container">

    <div class="barraNavegacio">
        <h6><a href="<?php echo APP_INTRANET; ?>">Intranet</a> > <a href="<?php echo APP_INTRANET . $url['historia']; ?>">Base de dades Història</a> > <a href="<?php echo APP_INTRANET . $url['historia']; ?>/llistat-esdeveniments">Llistat d'esdeveniments</a> </h6>
    </div>

    <main>
        <div class="container contingut">

            <h1>Base de dades d'Història: llistat d'esdeveniments</h1>

            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['historia']; ?>/nou-esdeveniment/'" class="button btn-gran btn-secondari">Afegir esdeveniment</button>

            <!-- Filtros -->
            <div class="filters">
                <h4>Selecciona una Etapa Històrica:</h4>
                <div id="etapas-buttons">
                    <!-- Los botones de etapas históricas se generarán dinámicamente -->
                    <button class="filter-btn etapa-btn" data-etapa="1">Prehistòria</button>
                    <button class="filter-btn etapa-btn" data-etapa="2">Edat Antiga</button>
                    <button class="filter-btn etapa-btn" data-etapa="3">Edat Mitjana</button>
                    <button class="filter-btn etapa-btn" data-etapa="4">Edat Moderna</button>
                    <button class="filter-btn etapa-btn" data-etapa="5">Edat Contemporània</button>
                    <button class="filter-btn etapa-btn" data-etapa="6">Món Actual</button>
                </div>

                <!-- Fila de botones de subetapas -->
                <div id="subetapas-buttons" style="display: none;margin-bottom:25px">
                    <h4>Selecciona una Subetapa:</h4>
                    <!-- Los botones de subetapas se generarán dinámicamente -->
                </div>
            </div>

            <div class="table-container" id="table-container">
            </div>

        </div>
    </main>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const etapaButtons = document.querySelectorAll('.etapa-btn');

        etapaButtons.forEach(button => {
            button.addEventListener('click', () => {
                etapaButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                const etapaSeleccionada = button.getAttribute('data-etapa');
                limpiarTabla();
                generarTabla();
                cargarEventosPorEtapa(etapaSeleccionada);
                cargarSubetapas(etapaSeleccionada);
            });
        });

        document.getElementById('subetapas-buttons').addEventListener('click', (event) => {
            if (event.target && event.target.classList.contains('subetapa-btn')) {
                document.querySelectorAll('.subetapa-btn').forEach(btn => btn.classList.remove('active'));
                event.target.classList.add('active');

                const subetapaId = event.target.getAttribute('data-subetapa');
                const etapaId = getSelectedEtapa();
                filtrarEventos(etapaId, subetapaId);
            }
        });
    });

    let todosLosEventos = [];
    let eventosPorPagina = 15;
    let paginaActual = 1;

    function getSelectedEtapa() {
        const selected = document.querySelector('.etapa-btn.active');
        return selected ? selected.getAttribute('data-etapa') : '1';
    }

    function generarTabla() {
        const container = document.getElementById('table-container');
        if (document.getElementById('authorsTable')) return;

        const table = document.createElement('table');
        table.id = 'authorsTable';
        table.className = 'table table-striped';

        const thead = document.createElement('thead');
        thead.className = 'table-primary';
        thead.innerHTML = `
        <tr>
            <th>Esdeveniment</th>
            <th>Ciutat</th>
            <th>Data</th>
            <th>Etapa històrica</th>
            <th>Època</th>
            <th></th>
            <th></th>
        </tr>`;
        table.appendChild(thead);

        const tbody = document.createElement('tbody');
        table.appendChild(tbody);

        container.appendChild(table);
    }

    function limpiarTabla() {
        const tbody = document.querySelector('#authorsTable tbody');
        if (tbody) tbody.innerHTML = '';
    }

    function cargarEventosPorEtapa(etapa) {
        fetch(`/api/historia/get/?llistatEsdeveniments&etapa=${etapa}`)
            .then(res => res.json())
            .then(data => renderTabla(data))
            .catch(() => mostrarErrorTabla());
    }

    function cargarSubetapas(etapa) {
        const container = document.getElementById('subetapas-buttons');
        container.style.display = 'block';
        container.innerHTML = '<h4>Selecciona una Subetapa:</h4>';

        fetch(`/api/historia/get/?subEtapesEtapa=${etapa}`)
            .then(res => res.json())
            .then(data => {
                if (data.length === 0 || data.error === 'No rows found') {
                    container.innerHTML += '<p>No hi ha subetapes disponibles</p>';
                    return;
                }

                data.forEach(subetapa => {
                    const btn = document.createElement('button');
                    btn.className = 'filter-btn subetapa-btn';
                    btn.textContent = subetapa.nomSubEtapa;
                    btn.setAttribute('data-subetapa', subetapa.id);
                    container.appendChild(btn);
                });
            })
            .catch(() => {
                container.innerHTML += '<p>Error carregant subetapes</p>';
            });
    }

    function filtrarEventos(etapa, subetapa) {
        fetch(`/api/historia/get/?llistatEsdeveniments&etapa=${etapa}&subetapa=${subetapa}`)
            .then(res => res.json())
            .then(data => renderTabla(data))
            .catch(() => mostrarErrorTabla());
    }

    function renderTabla(data) {
        const tbody = document.querySelector('#authorsTable tbody');
        if (!tbody) return;

        if (data.error === 'No rows found' || !data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">No hi ha resultats disponibles</td></tr>';
            return;
        }

        todosLosEventos = data.data;
        paginaActual = 1;
        renderPagina();
        renderControlesPaginacion();
    }

    function renderPagina() {
        const tbody = document.querySelector('#authorsTable tbody');
        if (!tbody) return;

        const inicio = (paginaActual - 1) * eventosPorPagina;
        const fin = inicio + eventosPorPagina;
        const eventosPagina = todosLosEventos.slice(inicio, fin);

        tbody.innerHTML = eventosPagina.map(evento => {
            const dataInici = formatData(evento.esdeDataIDia, evento.esdeDataIMes, evento.esdeDataIAny);
            const dataFi = formatData(evento.esdeDataFDia, evento.esdeDataFMes, evento.esdeDataFAny);

            let dataFinal = dataInici;
            if (dataFi && dataFi !== dataInici) {
                dataFinal += ` - ${dataFi}`;
            }

            return `
        <tr>
            <td><a href="./fitxa-esdeveniment/${evento.slug}" title="Esdeveniment">${evento.esdeNom}</a></td>
            <td>${evento.ciutat} (${evento.pais_cat})</td>
             <td>${dataFinal}</td>
            <td>${evento.etapaNom}</td>
            <td>${evento.nomSubEtapa}</td>
            <td><a href="https://${window.location.host}/gestio/historia/modifica-esdeveniment/${evento.slug}">
                <button type="button" class="button btn-petit">Modifica</button></td>
            <td></td>
          </tr>`;
        }).join('');
    }

    function getMesCatalan(numero) {
        const mesos = [
            "", "gener", "febrer", "març", "abril", "maig", "juny",
            "juliol", "agost", "setembre", "octubre", "novembre", "desembre"
        ];
        return mesos[numero] || "";
    }

    function formatData(dia, mes, any) {
        if (!any || any === 0) return "";

        const parts = [];

        if (dia && dia !== 0) parts.push(dia);
        if (mes && mes !== 0) parts.push(getMesCatalan(parseInt(mes)));
        parts.push(any);

        return parts.join(" ");
    }

    function renderControlesPaginacion() {
        const container = document.getElementById('table-container');
        let paginacion = document.getElementById('paginacion');

        if (!paginacion) {
            paginacion = document.createElement('div');
            paginacion.id = 'paginacion';
            paginacion.style.marginTop = '20px';
            container.appendChild(paginacion);
        }

        const totalPaginas = Math.ceil(todosLosEventos.length / eventosPorPagina);
        paginacion.innerHTML = '';

        for (let i = 1; i <= totalPaginas; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = 'filter-btn';
            if (i === paginaActual) btn.classList.add('active');
            btn.addEventListener('click', () => {
                paginaActual = i;
                renderPagina();
                renderControlesPaginacion();
            });
            paginacion.appendChild(btn);
        }
    }


    function mostrarErrorTabla() {
        const tbody = document.querySelector('#authorsTable tbody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">Error al carregar els resultats</td></tr>';
        }
    }
</script>

<style>
    .filter-buttons {
        text-align: center;
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        /* Permite que los botones se distribuyan en varias filas si es necesario */
        gap: 10px;
        /* Espacio entre los botones en cada fila */
    }

    .filter-btn {
        padding: 10px 20px;
        margin: 10px 10px;
        font-size: 16px;
        border: 1px solid #007bff;
        background-color: #fff;
        color: #007bff;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .filter-btn:hover {
        background-color: #007bff;
        color: white;
    }

    .filter-btn:active {
        transform: scale(0.95);
    }

    .active {
        background-color: #007bff;
        color: white;
    }
</style>