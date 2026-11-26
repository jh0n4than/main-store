<?php
/*
Use this URL to print data in browser:
http://localhost:80/main-store/src/list_users.php
*/

  session_start();
   if (!isset($_SESSION['user_id'])) {
      header('Location: signin.html');
      exit();
   }

   include('../settings/database.php');

   $role_result = pg_query_params(
      $local_conn,
      'SELECT role FROM users WHERE id = $1',
      [$_SESSION['user_id']]
   );

   if ($role_result === false) {
      error_log('Failed to retrieve user role in list_users.php: ' . pg_last_error($local_conn));
      http_response_code(500);
      exit('Error: no fue posible verificar el rol del usuario.');
   }

   $current_user = pg_fetch_assoc($role_result);
   if ($current_user === false || $current_user['role'] !== 'admin') {
      header('Location: signin.html');
      exit();
   }

   // 1. Capturamos el filtro de la URL
   $status = $_GET['userStatus'] ?? '';   

   // 2. Armamos la condición WHERE para PostgreSQL (TRUE / FALSE)
   $where = "";
   if ($status === '1') {
       $where = "WHERE status = TRUE";
   } elseif ($status === '0') {
        $where = "WHERE status = FALSE";
   }


   // Get data frm users table
   $sql = "
    SELECT 
        id,
        firstname || ' ' || lastname AS fullname,
        mobile_phone,
        email,
        CASE WHEN status = true THEN 'Active' ELSE 'Inactive' END AS status,
        url_photo 
    FROM 
        users
    $where
"; 
   $res = pg_query($local_conn, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f8f6f2">
    <title>Usuarios | Main Store</title>
    <link rel="icon" type="image/jpeg" href="icons/main-store.jpg">
    <style>
        :root {
            color-scheme: light;
            --ink: #292821;
            --muted: #716f67;
            --red: #e72d32;
            --red-dark: #c91f27;
            --cream: #f8f6f2;
            --white: #fff;
            --line: #e9e5dd;
            --shadow: 0 18px 48px rgba(48, 40, 30, .08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-width: 320px;
            margin: 0;
            background: var(--cream);
            color: var(--ink);
            font-family: "Segoe UI", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .container {
            width: min(1120px, calc(100% - 48px));
            margin: 0 auto;
        }

        .site-header {
            border-bottom: 1px solid rgba(41, 40, 33, .07);
            background: rgba(248, 246, 242, .94);
        }

        .header-inner {
            display: flex;
            min-height: 78px;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -.5px;
        }

        .brand img {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
        }

        .brand-accent {
            color: var(--red);
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 24px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .header-nav a,
        .footer-link {
            transition: color .2s ease;
        }

        .header-nav a:hover,
        .footer-link:hover {
            color: var(--red-dark);
        }

        .button {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            border: 1px solid transparent;
            border-radius: 8px;
            background: var(--red);
            color: var(--white);
            font-size: 12px;
            font-weight: 700;
            transition: background .2s ease, transform .2s ease;
        }

        .button:hover {
            transform: translateY(-1px);
            background: var(--red-dark);
        }

        .button-secondary {
            border-color: var(--line);
            background: var(--white);
            color: var(--ink);
        }

        .button-secondary:hover {
            border-color: var(--ink);
            background: var(--white);
        }

        .page-main {
            padding: 52px 0 76px;
        }

        .page-heading {
            margin-bottom: 28px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 13px;
            color: var(--red-dark);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .eyebrow::before {
            width: 20px;
            height: 2px;
            background: currentColor;
            content: "";
        }

        h1 {
            margin: 0;
            font-size: clamp(32px, 4vw, 44px);
            letter-spacing: -1.8px;
        }

        .page-description {
            margin: 10px 0 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.65;
        }

        .users-card {
            overflow: hidden;
            border: 1px solid rgba(233, 229, 221, .9);
            border-radius: 16px;
            background: var(--white);
            box-shadow: var(--shadow);
        }

        .card-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 22px;
            padding: 22px 24px;
            border-bottom: 1px solid var(--line);
        }

        .card-title {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
        }

        .card-subtitle {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: 12px;
        }

        .filter-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-form label {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .filter-form select {
            min-height: 40px;
            padding: 0 34px 0 12px;
            border: 1px solid var(--line);
            border-radius: 7px;
            background: var(--white);
            color: var(--ink);
            font: inherit;
            font-size: 12px;
        }

        .filter-form select:focus-visible,
        a:focus-visible {
            outline: 3px solid rgba(231, 45, 50, .28);
            outline-offset: 3px;
        }

        .table-scroll {
            overflow-x: auto;
        }

        .users-table {
            width: 100%;
            min-width: 760px;
            border-collapse: collapse;
            text-align: left;
        }

        .users-table th {
            padding: 15px 18px;
            background: #fbfaf8;
            color: var(--muted);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .8px;
            text-transform: uppercase;
        }

        .users-table td {
            padding: 15px 18px;
            border-top: 1px solid #f0ede7;
            font-size: 12px;
            vertical-align: middle;
        }

        .users-table tbody tr {
            transition: background .15s ease;
        }

        .users-table tbody tr:hover {
            background: #fdfcfb;
        }

        .user-photo {
            display: block;
            width: 42px;
            height: 42px;
            border: 2px solid #f0ede7;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-name {
            color: var(--ink);
            font-weight: 700;
        }

        .user-email,
        .user-phone {
            color: var(--muted);
        }

        .status-badge {
            display: inline-flex;
            min-height: 25px;
            align-items: center;
            padding: 0 10px;
            border-radius: 999px;
            background: #eaf3e8;
            color: #52754d;
            font-size: 10px;
            font-weight: 800;
        }

        .status-badge.inactive-marker {
            background: #fbe9e7;
            color: #a8463d;
        }

        .row-actions {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .icon-link {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border: 1px solid transparent;
            border-radius: 7px;
            transition: background .15s ease, border-color .15s ease;
        }

        .icon-link:hover {
            border-color: var(--line);
            background: #f8f6f2;
        }

        .icon-link img {
            display: block;
            width: 19px;
            height: 19px;
            object-fit: contain;
        }

        .confirm-backdrop {
            position: fixed;
            z-index: 5;
            display: grid;
            padding: 20px;
            background: rgba(41, 40, 33, .42);
            inset: 0;
            place-items: center;
        }

        .confirm-dialog {
            width: min(100%, 380px);
            padding: 24px;
            border: 1px solid var(--line);
            border-radius: 15px;
            background: var(--white);
            box-shadow: 0 24px 70px rgba(30, 25, 20, .2);
            text-align: center;
        }

        .confirm-dialog legend {
            padding: 0 8px;
            font-size: 17px;
            font-weight: 800;
        }

        .confirm-dialog p {
            margin: 4px 0 20px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .confirm-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .confirm-actions .button {
            min-height: 38px;
        }

        .site-footer {
            padding: 20px 0;
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: 11px;
        }

        .footer-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        @media (max-width: 700px) {
            .container {
                width: min(100% - 32px, 560px);
            }

            .header-inner {
                min-height: 70px;
            }

            .header-nav {
                display: none;
            }

            .page-main {
                padding-top: 38px;
            }

            .card-toolbar {
                align-items: flex-start;
                flex-direction: column;
                gap: 17px;
                padding: 19px;
            }

            .filter-form {
                width: 100%;
                justify-content: space-between;
            }

            .filter-form select {
                flex: 1;
                max-width: 230px;
            }
        }

        @media (max-width: 420px) {
            .container {
                width: calc(100% - 28px);
            }

            .brand {
                gap: 7px;
                font-size: 16px;
            }

            .brand img {
                width: 39px;
                height: 39px;
            }

            .button {
                padding: 0 12px;
                font-size: 11px;
            }

            .page-main {
                padding: 31px 0 52px;
            }

            .users-card {
                border-radius: 12px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="home.php" aria-label="Main Store, inicio">
                <img src="icons/main-store.jpg" alt="">
                <span><span class="brand-accent">Main</span> Store</span>
            </a>
            <nav class="header-nav" aria-label="Navegación principal">
                <a href="home.php">Panel principal</a>
                <a href="list_users.php" aria-current="page">Usuarios</a>
            </nav>
            <a class="button button-secondary" href="home.php">Volver al panel <span aria-hidden="true">&nbsp;→</span></a>
        </div>
    </header>

    <main class="container page-main">
        <section class="page-heading" aria-labelledby="page-title">
            <p class="eyebrow">Administración</p>
            <h1 id="page-title">Usuarios</h1>
            <p class="page-description">Consulta y administra las cuentas registradas en Main Store.</p>
        </section>

        <section class="users-card" aria-labelledby="users-card-title">
            <div class="card-toolbar">
                <div>
                    <h2 class="card-title" id="users-card-title">Directorio de usuarios</h2>
                    <p class="card-subtitle">Revisa la información y el estado de cada cuenta.</p>
                </div>
                <form class="filter-form" method="GET" action="list_users.php">
                    <label for="user-status">Filtrar por estado</label>
                    <select id="user-status" name="userStatus" onchange="this.form.submit()">
                        <option value="" <?= $status === '' ? 'selected' : '' ?>>
                            Todos los usuarios
                        </option>
                        <option value="1" <?= $status === '1' ? 'selected' : '' ?>>
                            Usuarios activos
                        </option>
                        <option value="0" <?= $status === '0' ? 'selected' : '' ?>>
                            Usuarios inactivos
                        </option>
                    </select>
                </form>
            </div>

            <div class="table-scroll">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th scope="col">Foto</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Correo electrónico</th>
                            <th scope="col">Teléfono</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = pg_fetch_assoc($res)) {?>
                        <tr>
                            <td>
                                <?php if ($row['url_photo'] != "") { ?>
                                    <img class="user-photo" src="<?php echo $row['url_photo']; ?>" alt="">
                                <?php } else { ?>
                                    <span class="user-phone">Sin foto</span>
                                <?php } ?>
                            </td>
                            <td class="user-name"><?= $row['fullname']?></td>
                            <td class="user-email"><?= $row['email']?></td>
                            <td class="user-phone"><?= $row['mobile_phone']?></td>
                            <td>
                                <?php if ($row['status'] == 'Active') { ?>
                                    <span class="status-badge">Activo</span>
                                <?php } else { ?>
                                    <span class="status-badge inactive-marker">Inactivo</span>
                                <?php } ?>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="icon-link" href="edit_user.php?id=<?php echo $row['id']; ?>" aria-label="Editar usuario" title="Editar usuario">
                                        <img src="icons/pencil.png" alt="">
                                    </a>
                                    <a class="icon-link" href="icons/search.png" aria-label="Ver imagen de búsqueda" title="Ver imagen">
                                        <img src="icons/search.png" alt="">
                                    </a>
                                    <a class="icon-link" href="?confirmar=1&id=<?= $row['id'] ?>" aria-label="Eliminar usuario" title="Eliminar usuario">
                                        <img src="icons/Trash.png" alt="">
                                    </a>
                                    <?php
                                        // Si llega el parámetro confirmar por la URL, la variable será true
                                        $mostrarConfirmacion = isset($_GET['confirmar']) && $_GET['confirmar'] == '1';
                                    ?>
                                    <?php if ($mostrarConfirmacion && $_GET['id'] == $row['id']): ?>
                                        <div class="confirm-backdrop">
                                            <fieldset class="confirm-dialog" role="alertdialog" aria-modal="true" aria-labelledby="confirm-title">
                                                <legend id="confirm-title">Confirmar eliminación</legend>
                                                <p>¿Quieres eliminar esta cuenta? Esta acción no se puede deshacer.</p>
                                                <div class="confirm-actions">
                                                    <a class="button" href="delete_user.php?userID=<?= $row['id'] ?>">Sí, eliminar</a>
                                                    <a class="button button-secondary" href="list_users.php">Cancelar</a>
                                                </div>
                                            </fieldset>
                                        </div>
                                    <?php endif; ?>
                                    <a class="icon-link" href="update_user.php?userID=<?= $row['id'] ?>&userStatus=<?= $row['status'] ?>" aria-label="<?= $row['status'] == 'Active' ? 'Desactivar usuario' : 'Activar usuario' ?>" title="<?= $row['status'] == 'Active' ? 'Desactivar usuario' : 'Activar usuario' ?>">
                                        <?php
                                        if ($row['status'] == 'Active'){
                                            echo "<img src='icons/inactive.png' alt=''>";
                                        }else{
                                            echo "<img src='icons/active.png' alt=''>";
                                        }
                                        ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php }?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <span>© 2026 Main Store</span>
            <a class="footer-link" href="home.php">Volver al panel principal</a>
        </div>
    </footer>
</body>
</html>