<?php

// http://localhost/main-store/src/edit_user.php 

session_start();
    if (!isset($_SESSION['user_id'])){
        header('refresh:0;url=signin.html');
    }else{
      //  header('refresh:0;url=home.php');
    }

    
include('../settings/database.php');

$id = $_GET['id'];   // el número que llegó en la URL

$sql = 'SELECT * FROM users WHERE id = $1';
$result = pg_query_params($local_conn, $sql, array($id));
$user = pg_fetch_assoc($result);   // una sola fila: el usuario a editar
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f8f6f2">
    <title>Editar usuario | Main Store</title>
    <link rel="icon" type="image/jpeg" href="icons/main-store.jpg">
    <link rel="stylesheet" href="auth.css">
    <style>
        .profile-photo {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 0 0 18px;
            padding: 12px;
            border: 1px solid #e9e5dd;
            border-radius: 10px;
            background: #fbfaf8;
        }

        .profile-photo img {
            width: 68px;
            height: 68px;
            border: 2px solid #e9e5dd;
            border-radius: 50%;
            object-fit: cover;
        }

        .profile-photo figcaption {
            color: #716f67;
            font-size: 12px;
            font-weight: 700;
        }

        .form-field input[type="file"].file-input {
            height: auto;
            padding: 9px;
            background: #fbfaf8;
        }

        .file-input::file-selector-button {
            margin-right: 10px;
            padding: 8px 11px;
            border: 0;
            border-radius: 6px;
            background: #fff0ef;
            color: #c91f27;
            cursor: pointer;
            font: inherit;
            font-size: 12px;
            font-weight: 700;
        }

        .form-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 23px;
        }

        .form-actions .submit-button {
            width: auto;
            flex: 1;
            margin: 0;
        }

        .cancel-link {
            display: inline-flex;
            min-height: 48px;
            align-items: center;
            justify-content: center;
            padding: 0 17px;
            border: 1px solid #e9e5dd;
            border-radius: 7px;
            color: #292821;
            font-size: 13px;
            font-weight: 700;
            transition: border-color .2s ease, background .2s ease;
        }

        .cancel-link:hover {
            border-color: #292821;
            background: #fbfaf8;
        }

        @media (max-width: 520px) {
            .form-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .form-actions .submit-button,
            .cancel-link {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="auth-header">
        <a class="brand" href="home.php" aria-label="Main Store, inicio">
            <img src="icons/main-store.jpg" alt="">
            <span>Main</span> Store
        </a>
        <a class="back-link" href="list_users.php">Volver a usuarios <span aria-hidden="true">→</span></a>
    </header>

    <main class="auth-layout">
        <section class="welcome-panel">
            <p class="eyebrow">Administración de cuenta</p>
            <h1>Actualiza la información del usuario.</h1>
            <p class="welcome-copy">
                Mantén los datos de la cuenta al día y actualiza la foto
                cuando lo necesites.
            </p>
            <div class="welcome-art" aria-hidden="true">
                <div class="art-circle"></div>
                <div class="art-bag"><span>✎</span></div>
                <span class="sparkle sparkle-one">✦</span>
                <span class="sparkle sparkle-two">✧</span>
                <span class="art-caption">Perfil de usuario</span>
            </div>
            <p class="panel-footnote"><span aria-hidden="true">✓</span> Revisa los cambios antes de guardarlos</p>
        </section>

        <section class="form-card" aria-labelledby="form-title">
            <p class="eyebrow">Perfil de usuario</p>
            <h2 id="form-title">Editar usuario</h2>
            <p class="form-intro">Actualiza la información de la cuenta seleccionada.</p>

            <form action="update_user.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">

                <div class="field-grid">
                    <div class="form-field">
                        <label for="firstname">Nombre</label>
                        <input id="firstname" type="text" name="firstname" value="<?php echo $user['firstname']; ?>" autocomplete="given-name" required>
                    </div>
                    <div class="form-field">
                        <label for="lastname">Apellido</label>
                        <input id="lastname" type="text" name="lastname" value="<?php echo $user['lastname']; ?>" autocomplete="family-name" required>
                    </div>
                    <div class="form-field field-full">
                        <label for="email">Correo electrónico</label>
                        <input id="email" type="email" name="email" value="<?php echo $user['email']; ?>" autocomplete="email" required>
                    </div>
                    <div class="form-field field-full">
                        <label for="mobile_phone">Teléfono móvil</label>
                        <input id="mobile_phone" type="text" name="mobile_phone" value="<?php echo $user['mobile_phone']; ?>" autocomplete="tel" required>
                    </div>
                </div>

                <?php if ($user['url_photo'] != "") { ?>
                    <figure class="profile-photo">
                        <img src="<?php echo $user['url_photo']; ?>" alt="Foto actual del usuario">
                        <figcaption>Foto actual</figcaption>
                    </figure>
                <?php } ?>

                <div class="form-field">
                    <label for="photo">Foto de perfil (JPG o PNG)</label>
                    <input class="file-input" id="photo" type="file" name="photo" accept=".jpg,.jpeg,.png">
                </div>

                <div class="form-actions">
                    <button class="submit-button" type="submit">Guardar cambios <span aria-hidden="true">→</span></button>
                    <a class="cancel-link" href="list_users.php">Cancelar</a>
                </div>
            </form>
        </section>
    </main>

    <footer class="auth-footer">
        <span>© 2026 Main Store</span>
        <a class="back-link" href="home.php">Volver al panel</a>
    </footer>
</body>
</html>