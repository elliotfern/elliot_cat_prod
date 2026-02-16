    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Elliot Fernandez - Registre</title>
        <script
            src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
            crossorigin="anonymous"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

        <style>
            body {
                background-color: #3c3c3c !important;

            }
        </style>
    </head>

    <body>


        <div class="container" style="margin-top:50px">
            <div class="card mx-auto" style="max-width: 400px;">
                <div class="card-body">
                    <div class="container">
                        <h1>Registre Usuari</h1>
                        <?php
                        echo '<div class="alert alert-success" id="loginMessageOk" style="display:none" role="alert"></div>';

                        echo '<div class="alert alert-danger" id="loginMessageErr" style="display:none" role="alert"></div>';
                        ?>

                        <form action="" method="post" id="registre" class="login">
                            <label for="username">Nom usuari</label>
                            <input type="text" name="username" id="username" class="form-control">
                            <br>

                            <label for="password">Contrasenya</label>
                            <input type="password" name="password" id="password" class="form-control">
                            <br>

                            <label for="firstName">Nom</label>
                            <input type="text" name="firstName" id="firstName" class="form-control">
                            <br>

                            <label for="lastName">Cognoms</label>
                            <input type="text" name="lastName" id="lastName" class="form-control">
                            <br>

                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control">
                            <br>

                            <button name="login" id="btnRegistre" class="btn btn-primary">Registre</button>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

        <script>
            document.getElementById('registre').addEventListener('submit', function(event) {
                event.preventDefault(); // Evitar el envío del formulario por defecto

                // Obtener los valores del formulario
                let username = document.getElementById('username').value;
                let password = document.getElementById('password').value;
                let firstName = document.getElementById('firstName').value;
                let lastName = document.getElementById('lastName').value;
                let email = document.getElementById('email').value;
                const loginMessageOk = document.getElementById('loginMessageOk');
                const loginMessageErr = document.getElementById('loginMessageErr');

                // Hacer la llamada AJAX para registrar al usuario
                let xhr = new XMLHttpRequest();
                xhr.open("POST", "/api/auth/registre", true);
                xhr.setRequestHeader("Content-Type", "application/json");

                // Enviar los datos como JSON
                xhr.send(JSON.stringify({
                    username: username,
                    password: password,
                    firstName: firstName,
                    lastName: lastName,
                    email: email
                }));

                // Manejar la respuesta
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // Parsear la respuesta JSON
                        let response = JSON.parse(xhr.responseText);

                        if (response.success) {
                            loginMessageOk.style.display = 'block';
                            loginMessageOk.innerHTML = response.message;
                            loginMessageErr.style.display = 'none';
                            setTimeout(() => {
                                window.location.href = "/ca/entrada";
                            }, 3000);
                        } else {
                            loginMessageErr.style.display = 'block';
                            loginMessageErr.innerHTML = response.message;
                            loginMessageOk.style.display = 'none';
                        }
                    } else {
                        // Si no es 200, mostrar un mensaje de error genérico
                        loginMessageErr.style.display = 'block';
                        loginMessageOk.style.display = 'none';
                        loginMessageErr.innerHTML = 'Error al registrar el usuario, por favor intenta nuevamente.';
                    }
                };

                // Manejar errores de la petición
                xhr.onerror = function() {
                    loginMessageErr.style.display = 'block';
                    loginMessageErr.innerHTML = 'Error de conexión, por favor intenta nuevamente.';
                };
            });
        </script>

    </body>

    </html>