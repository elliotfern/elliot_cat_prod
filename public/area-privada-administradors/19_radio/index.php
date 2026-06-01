<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">
            <h1>Ràdio online</h1>

            <div id="isAdminButton" style="display: none;">
                <?php if (isUserAdmin()) : ?>
                    <p>
                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['usuaris']; ?>/nou-usuari'" class="button btn-gran btn-secondari">Nou usuari</button>
                    </p>
                <?php endif; ?>
            </div>

            <div class="player">
                <img class="logo" src="https://elliot.cat/dist/rairadio3.png" alt="Rai Radio 3">
                <h2>Rai Radio 3</h2>
                <small>Audio en vivo</small>

                <div id="programa"><em>Cargando programa...</em></div>
                <div id="descripcion"></div>

                <div id="horarios" style="font-size: 0.9em; color: #555; margin-top: 8px;"></div>

                <button id="btnActualizar" style="margin: 10px 0;">Actualizar info</button>

                <audio id="audio" autoplay>
                    <source src="https://icecdn-19d24861e90342cc8decb03c24c8a419.msvdn.net/icecastRelay/S56630579/yEbkcBtIoSwd/icecast" type="audio/mpeg">
                    Tu navegador no soporta el audio.
                </audio>

                <div class="controls">
                    <button onclick="audio.play()">▶️</button>
                    <button onclick="audio.pause()">⏸️</button>
                    <button onclick="audio.muted = !audio.muted">🔇</button>
                    <button onclick="audio.volume = Math.min(1, audio.volume + 0.1)">🔊</button>
                    <button onclick="audio.volume = Math.max(0, audio.volume - 0.1)">🔉</button>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
    let timeoutId;

    document.getElementById('btnActualizar').addEventListener('click', () => {
        actualizarPrograma();
    });

    async function actualizarPrograma() {
        try {
            const response = await fetch('https://www.raiplaysound.it/palinsesto/onAir.json');
            const data = await response.json();

            const radio3 = data.on_air[2];
            const item = radio3.currentItem;

            // Mostrar nombre y descripción
            document.getElementById('programa').innerText = item.name || "Programa desconocido";
            document.getElementById('descripcion').innerText = item.episode_title || "";

            // Calcular hora de inicio y fin
            const ahora = new Date();
            const [h, m] = item.hour.split(":").map(Number);
            const [dh, dm, ds] = item.duration.split(":").map(Number);

            const inicio = new Date(ahora);
            inicio.setHours(h, m, 0, 0);
            if (inicio > ahora) {
                inicio.setDate(inicio.getDate() - 1);
            }

            const fin = new Date(inicio);
            fin.setHours(fin.getHours() + dh);
            fin.setMinutes(fin.getMinutes() + dm);
            fin.setSeconds(fin.getSeconds() + ds);

            // Mostrar horarios formateados HH:MM
            const formatHHMM = d => d.toTimeString().slice(0, 5);
            document.getElementById('horarios').innerText = `Horario: ${formatHHMM(inicio)} - ${formatHHMM(fin)}`;

            // Programar próxima actualización justo al acabar el programa
            const msHastaFin = fin - ahora;
            clearTimeout(timeoutId);
            if (msHastaFin > 0) {
                timeoutId = setTimeout(actualizarPrograma, msHastaFin + 2000);
            } else {
                timeoutId = setTimeout(actualizarPrograma, 2 * 60 * 1000);
            }

        } catch (error) {
            console.error("Error al obtener datos del programa:", error);
            document.getElementById('programa').innerText = "Error al cargar programa";
            document.getElementById('descripcion').innerText = "";
            document.getElementById('horarios').innerText = "";
            timeoutId = setTimeout(actualizarPrograma, 10 * 60 * 1000);
        }
    }

    actualizarPrograma();

    // Actualización extra cada 15 minutos para mantener datos frescos
    setInterval(() => {
        console.log("Actualización periódica cada 15 minutos");
        actualizarPrograma();
    }, 15 * 60 * 1000);
</script>



<style>
    .player {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        padding: 20px;
        text-align: center;
        max-width: 320px;
    }

    .logo {
        width: 100%;
        max-height: 180px;
        object-fit: contain;
        margin-bottom: 20px;
    }

    .controls {
        display: flex;
        justify-content: space-around;
        margin-top: 10px;
    }

    button {
        background-color: rgb(117, 69, 37);
        border: none;
        color: white;
        padding: 10px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.2s ease;
    }

    button:hover {
        background-color: #004c99;
    }


    small {
        color: gray;
    }
</style>