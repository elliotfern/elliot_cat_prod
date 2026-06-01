<main>
    <div class="form">
        <h2>Mastodon</h2>

        <button onclick="refreshFeed()" style="margin-bottom: 10px; padding: 5px 10px;">🔄 Refrescar</button>
        <div id="loading-message" style="display: none;">Llegint nous posts...</div>
        <div id="feed"></div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        refreshFeed(); // Llamada a la función cuando el DOM esté completamente cargado
    });

    // Función para refrescar el feed
    async function refreshFeed() {
        const feedContainer = document.getElementById('feed');
        const loadingMessage = document.getElementById('loading-message');

        // Mostrar mensaje de carga
        loadingMessage.style.display = 'block';

        try {
            // Llamada a la API
            const response = await fetch('/api/xarxes-socials/get/mastodont/?type=feed-mastodon', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                },
            });

            const feed = await response.json(); // Suponiendo que el feed devuelve JSON

            feedContainer.innerHTML = ''; // Limpiar el contenido anterior

            // Ocultar mensaje de carga
            loadingMessage.style.display = 'none';

            if (feed && feed.length > 0) {
                feed.forEach(post => {
                    let boostedBy = post.reblog ? post.reblog.account.display_name : null;
                    let name = post.account.display_name;
                    let username = post.account.acct;
                    let avatar = post.account.avatar;
                    let content = post.content;
                    let postId = post.id;
                    let mastodon = "mastodont.cat";
                    let profileUrl = `https://${mastodon}/@${username}`;

                    // 📅 Convertir fecha a un formato legible
                    let date = new Date(post.created_at);
                    let formattedDate = date.toLocaleString('es-ES', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                    });

                    const postElement = document.createElement('div');
                    // Asignar un id único al postElement
                    postElement.id = `post-${postId}`;
                    postElement.style.border = '1px solid black';
                    postElement.style.padding = '10px';
                    postElement.style.margin = '10px';

                    // 📌 Avatar con enlace al perfil
                    postElement.innerHTML += `
                    <a href="${profileUrl}" target="_blank" style="text-decoration: none; color: inherit;">
                        <img src="${avatar}" alt="${name}" width="50" height="50" style="border-radius: 50%; margin-right: 10px;">
                    </a>
                    `;

                    // 📌 Nombre de usuario con enlace
                    postElement.innerHTML += `
                    <a href="${profileUrl}" target="_blank" style="text-decoration: none; color: inherit; font-weight: bold;">
                        ${name} (@${username})
                    </a><br>
                    `;

                    if (boostedBy) {
                        postElement.innerHTML += `
                            <p style="color: gray;">
                                🔁 Creat per <strong>${boostedBy}</strong>
                            </p>
                            <div style="margin-left: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                <p><em>Contingut original:</em></p>
                                <p>${post.reblog.content}</p>
                                ${post.reblog.media_attachments && post.reblog.media_attachments.length > 0
                                    ? post.reblog.media_attachments.map(media => `
                                        <a href="${media.url}" target="_blank">
                                            <img src="${media.preview_url}" alt="Media" style="max-width: 100%; border-radius: 5px; margin-bottom: 5px;">
                                        </a>
                                    `).join('')
                                    : ''
                                }
                            </div>
                        `;
                    }

                    postElement.innerHTML += `<small style="color: gray;">📅 ${formattedDate}</small><br>`;
                    postElement.innerHTML += `<p>${content}</p>`;

                    // 📸 Mostrar imágenes adjuntas
                    if (post.media_attachments && post.media_attachments.length > 0) {
                        postElement.innerHTML += '<div style="margin-top: 10px;">';
                        post.media_attachments.forEach(media => {
                            let mediaUrl = media.preview_url;
                            let fullMediaUrl = media.url;
                            postElement.innerHTML += `
                            <a href="${fullMediaUrl}" target="_blank">
                                <img src="${mediaUrl}" alt="Adjunto" style="max-width: 100%; border-radius: 5px; margin-bottom: 5px;">
                            </a>
                        `;
                        });
                        postElement.innerHTML += '</div>';
                    }

                    // Botón de "Me gusta"
                    postElement.innerHTML += `<div style="margin-top: 10px;"><button onclick="likePost(${postId})">❤️ M'agrada</button></div>`;

                    feedContainer.appendChild(postElement); // Añade el post al contenedor
                });
            } else {
                feedContainer.innerHTML = 'No se pudo obtener el feed.';
            }
        } catch (error) {
            console.error('Error al refrescar el feed:', error);
            feedContainer.innerHTML = 'Error al cargar el feed.';
            loadingMessage.style.display = 'none'; // Ocultar mensaje de carga en caso de error
        }
    }

    function likePost(postId) {
        fetch('/api/xarxes-socials/post/likes-mastodont/?type=likes-mastodont', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    post_id: postId
                })
            })
            .then(response => response.json())
            .then(data => alert(data.message))
            .catch(error => console.error('Error:', error));
    }

    let replyFormVisible = false;
</script>