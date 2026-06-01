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
                <img class="logo" src="https://elliot.cat/dist/catmusica.png" alt="Rai Radio 3">
                <h2>Catalunya Música</h2>
                <small>Audio en vivo</small>

                <div id="programa"><em>Cargando programa...</em></div>
                <p>
                <div id="descripcion"></div>
                </p>

                <div id="horarios" style="font-size: 0.9em; color: #555; margin-top: 8px;"></div>

                <button id="btnActualizar" style="margin: 10px 0;">Actualizar info</button>

                <audio id="audio-player" controls>
                    <source src="https://directes-radio-int.3catdirectes.cat/live-content/catalunya-musica-hls/master.m3u8" type="application/x-mpegURL">
                    Tu navegador no soporta el formato HLS.
                </audio>
                <button id="volume-up">Subir volumen</button>
                <button id="volume-down">Bajar volumen</button>

            </div>

        </div>
    </main>
</div>


<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
    const audioPlayer = document.getElementById('audio-player');

    const volumeUpButton = document.getElementById('volume-up');
    const volumeDownButton = document.getElementById('volume-down');

    // Función para subir volumen
    volumeUpButton.addEventListener('click', () => {
        if (audioPlayer.volume < 1) {
            audioPlayer.volume += 0.1; // Aumentar volumen en incrementos de 0.1
        }
    });

    // Función para bajar volumen
    volumeDownButton.addEventListener('click', () => {
        if (audioPlayer.volume > 0) {
            audioPlayer.volume -= 0.1; // Disminuir volumen en incrementos de 0.1
        }
    });

    // Comprobamos si el navegador soporta HLS nativamente (por ejemplo, Safari)
    if (Hls.isSupported()) {
        const hls = new Hls();
        hls.loadSource('https://directes-radio-int.3catdirectes.cat/live-content/catalunya-musica-hls/master.m3u8');
        hls.attachMedia(audioPlayer);
        hls.on(Hls.Events.MANIFEST_PARSED, function() {
            audioPlayer.play(); // Intentamos reproducir el audio
        });
    } else if (audioPlayer.canPlayType('application/vnd.apple.mpegurl')) {
        // Safari y otros navegadores con soporte nativo para HLS
        audioPlayer.src = 'https://directes-radio-int.3catdirectes.cat/live-content/catalunya-musica-hls/master.m3u8';
        audioPlayer.play();
    }
</script>

<script>
    let timeoutId;

    // Función para limpiar el HTML de una cadena
    function limpiarHTML(cadena) {
        const div = document.createElement('div'); // Crea un div en el DOM
        div.innerHTML = cadena; // Asigna el contenido HTML
        return div.innerText || div.textContent; // Devuelve solo el texto limpio
    }

    document.getElementById('btnActualizar').addEventListener('click', () => {
        actualizarPrograma();
    });

    async function actualizarPrograma() {
        try {
            const response = await fetch('https://elliot.cat/api/radio/get');
            const data = await response.json();

            const item = data.canal[0].ara_fem;

            // Mostrar nombre y descripción
            document.getElementById('programa').innerHTML = `<strong>${item.titol_programa || "Programa desconocido"}</strong>`;
            document.getElementById('descripcion').innerHTML = item.sinopsi || "";
        } catch (error) {
            console.error("Error al obtener datos del programa:", error);
            document.getElementById('programa').innerText = "Error al cargar programa";
            document.getElementById('descripcion').innerText = "Error al cargar programa";

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