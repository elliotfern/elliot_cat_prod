<main>

    <div class="form">
        <h2>Publicar a Xarxes socials (Mastodon i Bluesky)</h2>
        <div class="alert alert-success" id="mensajeExito" style="display:none"></div>
        <div class="alert alert-danger" id="mensajeErr" style="display:none"></div>

        <form action="" method="POST" enctype="multipart/form-data" id="blueskyForm">
            <div class="form-espai">

                <div class="col-md-12">
                    <label for="mensaje">Missatge:</label>
                    <textarea id="mensaje" name="mensaje" rows="4" cols="50" required></textarea>
                    <p id="charCount"></p>
                </div>

                <div class="col-md-12">
                    <label for="imagen">Imatge (opcional):</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                </div>

                <div class="col-md-12">
                    <label for="altImatge">Alt imatge:</label>
                    <input type="text" name="altImatge" id="altImatge" placeholder="Escribe el texto alternativo para la imagen">
                </div>

                <div class="col-md-4">
                    <label>Selecciona on vols publicar:</label>

                    <label class="checkbox-container">
                        <input type="checkbox" id="publicarBluesky" name="redes" value="bluesky">
                        <span class="checkbox-custom"></span>
                        Bluesky
                    </label>

                    <label class="checkbox-container">
                        <input type="checkbox" id="publicarMastodon" name="redes" value="mastodon" checked>
                        <span class="checkbox-custom"></span>Mastodon
                    </label>

                    <label class="checkbox-container">
                        <input type="checkbox" id="publicarBlog" name="redes" value="blog" checked>
                        <span class="checkbox-custom"></span>Blog
                    </label>
                </div>

                <style>
                    /* Estilo base del contenedor */
                    .checkbox-container {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        cursor: pointer;
                        font-size: 16px;
                        font-weight: 500;
                        user-select: none;
                    }

                    /* Oculta el checkbox original */
                    .checkbox-container input[type="checkbox"] {
                        display: none;
                    }

                    /* Estilo del switch personalizado */
                    .checkbox-custom {
                        width: 40px;
                        height: 20px;
                        background: #ccc;
                        border-radius: 50px;
                        position: relative;
                        transition: background 0.3s ease-in-out;
                    }

                    /* La bolita dentro del switch */
                    .checkbox-custom::before {
                        content: "";
                        position: absolute;
                        width: 16px;
                        height: 16px;
                        background: white;
                        border-radius: 50%;
                        top: 2px;
                        left: 2px;
                        transition: transform 0.3s ease-in-out;
                    }

                    /* Si el checkbox está marcado */
                    .checkbox-container input[type="checkbox"]:checked+.checkbox-custom {
                        background: #007bff;
                    }

                    /* Mueve la bolita a la derecha cuando está activado */
                    .checkbox-container input[type="checkbox"]:checked+.checkbox-custom::before {
                        transform: translateX(20px);
                    }
                </style>
                <div class="col-md-4"> </div>
                <div class="col-md-4"> </div>

            </div>
            <hr class="separador">
            <div class="form-espai">
                <!-- Columna izquierda: Botón Atrás -->
                <div class="col-md-4">
                    <button type="button" id="" class=" btn-gran btn-enrere">Tornar enrere </button>
                </div>

                <!-- Columna derecha: Botón Crear factura -->
                <div class="col-md-4 dreta">
                    <button type="submit" id="submitMastodon" onclick="publicar()" class="btn-gran btn-primari">Publicar a xarxes</button>
                </div>
            </div>

    </div>
    </form>
    </div>
</main>

<script>
    document.getElementById("mensaje").addEventListener("input", function() {
        // Verifica si el checkbox de Bluesky está seleccionado
        if (document.getElementById("publicarBluesky").checked) {
            var maxLength = 300;
            var currentLength = this.value.length;
            var remaining = maxLength - currentLength;

            // Actualiza el contador
            document.getElementById("charCount").textContent = remaining + " caracteres restantes";
        } else if (document.getElementById("publicarMastodon").checked) {
            var maxLength = 500;
            var currentLength = this.value.length;
            var remaining = maxLength - currentLength;

            // Actualiza el contador
            document.getElementById("charCount").textContent = remaining + " caracteres restantes";
        } else {
            // Si no está seleccionado, oculta el contador o deja el mensaje vacío
            document.getElementById("charCount").textContent = "";
        }
    });


    function publicar() {
        event.preventDefault(); // Evitar el envío automático del formulario

        // Verificar qué redes están seleccionadas
        const publicarBluesky = document.getElementById("publicarBluesky").checked;
        const publicarMastodon = document.getElementById("publicarMastodon").checked;
        const publicarBlog = document.getElementById("publicarBlog").checked;

        // Llamar solo a las funciones de las redes seleccionadas
        if (publicarBluesky) publicarEnBluesky();
        if (publicarMastodon) publicarEnMastodon();
        if (publicarBlog) publicarEnBlog();
    }


    // Función para publicar en Bluesky
    function publicarEnBluesky() {
        event.preventDefault(); // Evitar el envío normal del formulario

        const formData = new FormData();
        formData.append("mensaje", document.getElementById("mensaje").value);


        const imageFile = document.getElementById("imagen").files[0];
        if (imageFile) {
            const validTypes = ["image/jpeg", "image/png", "image/gif"];
            if (!validTypes.includes(imageFile.type)) {
                alert("Por favor, selecciona una imagen válida (JPG, PNG, GIF).");
                return;
            }
            formData.append("imagen", imageFile); // Archivo
            formData.append("altImatge", document.getElementById("altImatge").value);
        }

        fetch("/api/xarxes-socials/post/bluesky/?type=blueSky", {
                method: "POST",
                body: formData, // Enviamos los datos del formulario
            })
            .then(response => response.json())
            .then(data => {
                // Obtener el div donde mostrar el mensaje de éxito
                const mensajeExitoDiv = document.getElementById('mensajeExito');
                const mensajeErrDiv = document.getElementById('mensajeErr');

                // Establecer el mensaje de éxito en el div
                if (data.success) {
                    mensajeExitoDiv.innerHTML = data.success;

                    // Mostrar el div (si estaba oculto)
                    mensajeExitoDiv.style.display = 'block';

                    // Opcional: puedes agregar un temporizador para ocultarlo después de unos segundos
                    setTimeout(() => {
                        mensajeExitoDiv.style.display = 'none';
                    }, 5000); // Oculta el mensaje después de 5 segundos
                } else {
                    mensajeErrDiv.innerHTML = data.error;
                    mensajeErrDiv.style.display = 'block';
                    // Opcional: puedes agregar un temporizador para ocultarlo después de unos segundos
                    setTimeout(() => {
                        mensajeErrDiv.style.display = 'none';
                    }, 5000); // Oculta el mensaje después de 5 segundos
                }
            })
            .catch(error => console.error("Error:", error));
    }

    function publicarEnMastodon() {

        event.preventDefault(); // Evitar el envío normal del formulario

        const formData = new FormData();
        formData.append("mensaje", document.getElementById("mensaje").value);

        const imageFile = document.getElementById("imagen").files[0];
        if (imageFile) {
            const validTypes = ["image/jpeg", "image/png", "image/gif"];
            if (!validTypes.includes(imageFile.type)) {
                alert("Por favor, selecciona una imagen válida (JPG, PNG, GIF).");
                return;
            }
            formData.append("imagen", imageFile); // Archivo
            formData.append("altImatge", document.getElementById("altImatge").value);
        }

        fetch("/api/xarxes-socials/post/mastodont/?type=mastodont", {
                method: "POST",
                body: formData, // Enviamos los datos del formulario
            })
            .then(response => response.json())
            .then(data => {
                // Obtener el div donde mostrar el mensaje de éxito
                const mensajeExitoDiv = document.getElementById('mensajeExito');
                const mensajeErrDiv = document.getElementById('mensajeErr');

                // Establecer el mensaje de éxito en el div
                if (data.success) {
                    mensajeExitoDiv.innerHTML = data.success;

                    // Mostrar el div (si estaba oculto)
                    mensajeExitoDiv.style.display = 'block';

                    // Opcional: puedes agregar un temporizador para ocultarlo después de unos segundos
                    setTimeout(() => {
                        mensajeExitoDiv.style.display = 'none';
                    }, 5000); // Oculta el mensaje después de 5 segundos
                } else {
                    mensajeErrDiv.innerHTML = data.error;
                    mensajeErrDiv.style.display = 'block';
                    // Opcional: puedes agregar un temporizador para ocultarlo después de unos segundos
                    setTimeout(() => {
                        mensajeErrDiv.style.display = 'none';
                    }, 5000); // Oculta el mensaje después de 5 segundos
                }
            })
            .catch(error => console.error("Error:", error));

    }


    function publicarEnBlog() {

        event.preventDefault(); // Evitar el envío normal del formulario

        const formData = new FormData();
        formData.append("mensaje", document.getElementById("mensaje").value);


        fetch("/api/xarxes-socials/post/blog/?type=blog", {
                method: "POST",
                body: formData, // Enviamos los datos del formulario
            })
            .then(response => response.json())
            .then(data => {
                // Obtener el div donde mostrar el mensaje de éxito
                const mensajeExitoDiv = document.getElementById('mensajeExito');
                const mensajeErrDiv = document.getElementById('mensajeErr');

                // Establecer el mensaje de éxito en el div
                if (data.status === "success") {
                    mensajeExitoDiv.innerHTML = data.message;

                    // Mostrar el div (si estaba oculto)
                    mensajeExitoDiv.style.display = 'block';

                    // Opcional: puedes agregar un temporizador para ocultarlo después de unos segundos
                    setTimeout(() => {
                        mensajeExitoDiv.style.display = 'none';
                    }, 5000); // Oculta el mensaje después de 5 segundos
                } else {
                    mensajeErrDiv.innerHTML = data.error;
                    mensajeErrDiv.style.display = 'block';
                    // Opcional: puedes agregar un temporizador para ocultarlo después de unos segundos
                    setTimeout(() => {
                        mensajeErrDiv.style.display = 'none';
                    }, 5000); // Oculta el mensaje después de 5 segundos
                }
            })
            .catch(error => console.error("Error:", error));

    }
</script>