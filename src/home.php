<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('refresh:0;url=signin.html');
    exit();
}

include('../settings/database.php');

$role_result = pg_query_params(
    $local_conn,
    'SELECT role FROM users WHERE id = $1',
    [$_SESSION['user_id']]
);

if ($role_result === false) {
    error_log('Failed to retrieve user role in home.php: ' . pg_last_error($local_conn));
    http_response_code(500);
    exit('Error: no fue posible consultar el rol del usuario.');
}

$current_user = pg_fetch_assoc($role_result);
$is_admin = $current_user !== false && $current_user['role'] === 'admin';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f8f6f2">
    <title>Mi espacio | Main Store</title>
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
            --shadow: 0 24px 70px rgba(48, 40, 30, .11);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
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
            min-height: 82px;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 19px;
            font-weight: 800;
            letter-spacing: -.6px;
        }

        .brand img {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            object-fit: cover;
        }

        .brand-accent {
            color: var(--red);
        }

        .main-nav,
        .header-actions {
            display: flex;
            align-items: center;
            gap: 25px;
            font-size: 13px;
            font-weight: 700;
        }

        .main-nav {
            color: var(--muted);
        }

        .main-nav a,
        .text-link {
            transition: color .2s ease;
        }

        .main-nav a:hover,
        .text-link:hover {
            color: var(--red-dark);
        }

        .button {
            display: inline-flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            padding: 0 20px;
            border: 1px solid transparent;
            border-radius: 8px;
            background: var(--red);
            color: var(--white);
            font-size: 13px;
            font-weight: 700;
            transition: background .2s ease, transform .2s ease, box-shadow .2s ease;
        }

        .button:hover {
            transform: translateY(-2px);
            background: var(--red-dark);
            box-shadow: 0 8px 20px rgba(201, 31, 39, .2);
        }

        .button-secondary {
            border-color: var(--line);
            background: var(--white);
            color: var(--ink);
        }

        .button-secondary:hover {
            border-color: var(--ink);
            background: var(--white);
            color: var(--ink);
            box-shadow: none;
        }

        .hero {
            display: grid;
            min-height: 565px;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 48px;
            padding-top: 42px;
            padding-bottom: 54px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 18px;
            color: var(--red-dark);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .eyebrow::before {
            width: 22px;
            height: 2px;
            background: currentColor;
            content: "";
        }

        h1 {
            max-width: 550px;
            margin: 0;
            font-size: clamp(42px, 5vw, 64px);
            font-weight: 800;
            letter-spacing: -3.4px;
            line-height: 1.05;
        }

        h1 span {
            color: var(--red);
        }

        .hero-copy {
            max-width: 480px;
            margin: 20px 0 28px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.75;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .hero-note {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 27px;
            color: var(--muted);
            font-size: 12px;
        }

        .note-icon {
            display: grid;
            width: 27px;
            height: 27px;
            place-items: center;
            border-radius: 50%;
            background: #e7efe4;
            color: #52754d;
            font-weight: 800;
        }

        .hero-art {
            position: relative;
            display: grid;
            min-height: 410px;
            place-items: center;
            isolation: isolate;
        }

        .orbit {
            position: absolute;
            z-index: -1;
            width: min(380px, 86%);
            aspect-ratio: 1;
            border: 1px solid #e8dfd3;
            border-radius: 50%;
        }

        .orbit::before,
        .orbit::after {
            position: absolute;
            border: 1px solid #eee8df;
            border-radius: 50%;
            content: "";
            inset: 24px;
        }

        .orbit::after {
            inset: 50px;
        }

        .store-card {
            position: relative;
            width: min(340px, 76%);
            padding: 16px;
            transform: rotate(-3deg);
            border: 1px solid rgba(255, 255, 255, .8);
            border-radius: 20px;
            background: rgba(255, 255, 255, .82);
            box-shadow: var(--shadow);
            backdrop-filter: blur(8px);
        }

        .card-kicker {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 2px 3px 14px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
        }

        .card-image {
            display: grid;
            min-height: 215px;
            place-items: center;
            overflow: hidden;
            border-radius: 13px;
            background:
                radial-gradient(circle at 50% 43%, rgba(255,255,255,.92) 0 23%, transparent 24%),
                linear-gradient(145deg, #f8d9c7 0%, #f6e5d7 45%, #e8c7b1 100%);
        }

        .shopping-bag {
            position: relative;
            display: grid;
            width: 116px;
            height: 116px;
            margin-top: 22px;
            place-items: center;
            border-radius: 14px 14px 24px 24px;
            background: var(--red);
            box-shadow: 0 17px 30px rgba(160, 49, 39, .2);
            color: var(--white);
            font-size: 42px;
            font-weight: 800;
            transform: rotate(4deg);
        }

        .shopping-bag::before {
            position: absolute;
            top: -25px;
            width: 44px;
            height: 44px;
            border: 7px solid var(--red);
            border-bottom: 0;
            border-radius: 25px 25px 0 0;
            content: "";
        }

        .card-caption {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 3px 2px;
        }

        .card-caption strong {
            display: block;
            font-size: 14px;
        }

        .card-caption small {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 11px;
        }

        .floating-card {
            position: absolute;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 11px 14px;
            border: 1px solid rgba(255, 255, 255, .9);
            border-radius: 11px;
            background: var(--white);
            box-shadow: 0 12px 30px rgba(48, 40, 30, .1);
            font-size: 11px;
            font-weight: 700;
        }

        .floating-icon {
            display: grid;
            width: 32px;
            height: 32px;
            place-items: center;
            border-radius: 8px;
            background: #fff3df;
            font-size: 16px;
        }

        .floating-one {
            top: 54px;
            right: 0;
        }

        .floating-two {
            bottom: 46px;
            left: 0;
        }

        .benefits {
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, .62);
        }

        .benefit-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            padding-top: 27px;
            padding-bottom: 27px;
        }

        .benefit {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .benefit-icon {
            display: grid;
            width: 43px;
            height: 43px;
            flex: 0 0 auto;
            place-items: center;
            border-radius: 12px;
            background: #fff0ef;
            color: var(--red);
            font-size: 19px;
        }

        .benefit strong {
            display: block;
            font-size: 13px;
        }

        .benefit p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.5;
        }

        .welcome {
            padding: 68px 0 76px;
            text-align: center;
        }

        .welcome .eyebrow {
            justify-content: center;
        }

        .welcome h2 {
            margin: 0;
            font-size: clamp(29px, 4vw, 39px);
            letter-spacing: -1.5px;
        }

        .welcome-copy-bottom {
            max-width: 520px;
            margin: 13px auto 23px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
        }

        .site-footer {
            padding: 21px 0;
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

        @media (max-width: 760px) {
            .container {
                width: min(100% - 36px, 560px);
            }

            .header-inner {
                min-height: 72px;
            }

            .main-nav {
                display: none;
            }

            .header-actions {
                gap: 12px;
                font-size: 12px;
            }

            .header-actions .button {
                min-height: 40px;
                padding: 0 13px;
            }

            .hero {
                min-height: 0;
                grid-template-columns: 1fr;
                gap: 8px;
                padding-top: 54px;
                padding-bottom: 42px;
            }

            h1 {
                max-width: 520px;
                letter-spacing: -2.4px;
            }

            .hero-copy {
                font-size: 15px;
            }

            .hero-art {
                min-height: 370px;
            }

            .benefit-grid {
                grid-template-columns: 1fr;
                gap: 19px;
                padding-top: 23px;
                padding-bottom: 23px;
            }

            .welcome {
                padding: 58px 0;
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

            .header-actions {
                gap: 9px;
                font-size: 11px;
            }

            .header-actions .button {
                min-height: 38px;
                padding: 0 9px;
                font-size: 11px;
            }

            .hero {
                padding-top: 43px;
            }

            .hero-actions .button {
                flex: 1;
                padding: 0 12px;
            }

            .hero-art {
                min-height: 320px;
            }

            .floating-one {
                top: 31px;
            }

            .floating-two {
                bottom: 28px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

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
                <span class="brand-accent">Main</span> Store
            </a>
            <nav class="main-nav" aria-label="Navegación principal">
                <a href="#inicio">Mi espacio</a>
                <a href="../index.html">Tienda</a>
                <?php if ($is_admin): ?>
                    <a href="list_users.php">Administrar usuarios</a>
                <?php endif; ?>
            </nav>
            <div class="header-actions">
                <a class="button button-secondary" href="logout.php">Cerrar sesión</a>
            </div>
        </div>
    </header>

    <main>
        <section class="container hero" id="inicio">
            <div class="hero-content">
                <p class="eyebrow">Tu espacio en Main Store</p>
                <h1>¡Hola, <span><?= htmlspecialchars($_SESSION['user_firstname'] ?? 'bienvenido', ENT_QUOTES, 'UTF-8') ?></span>!</h1>
                <p class="hero-copy">
                    Nos alegra tenerte de vuelta. Explora Main Store y disfruta
                    de una experiencia de compra sencilla, a tu propio ritmo.
                </p>
                <div class="hero-actions">
                    <?php if ($is_admin): ?>
                        <a class="button" href="list_users.php">Administrar usuarios <span aria-hidden="true">&nbsp;→</span></a>
                    <?php else: ?>
                        <a class="button" href="../index.html">Explorar la tienda <span aria-hidden="true">&nbsp;→</span></a>
                    <?php endif; ?>
                    <a class="button button-secondary" href="#ventajas">Conocer más</a>
                </div>
                <div class="hero-note">
                    <span class="note-icon" aria-hidden="true">✓</span>
                    <span>Tu cuenta está lista para seguir explorando.</span>
                </div>
            </div>

            <div class="hero-art" role="img" aria-label="Ilustración decorativa de una bolsa de compras">
                <div class="orbit" aria-hidden="true"></div>
                <div class="store-card" aria-hidden="true">
                    <div class="card-kicker">
                        <span>Descubre Main Store</span>
                        <span>✦</span>
                    </div>
                    <div class="card-image">
                        <div class="shopping-bag">M</div>
                    </div>
                    <div class="card-caption">
                        <div>
                            <strong>Tu próxima compra empieza aquí</strong>
                            <small>Simple, cómoda y a tu manera</small>
                        </div>
                        <span class="note-icon" aria-hidden="true">→</span>
                    </div>
                </div>
                <div class="floating-card floating-one" aria-hidden="true">
                    <span class="floating-icon">✨</span>
                    Algo para ti
                </div>
                <div class="floating-card floating-two" aria-hidden="true">
                    <span class="floating-icon">🛍️</span>
                    Compra a tu ritmo
                </div>
            </div>
        </section>

        <section class="benefits" id="ventajas" aria-label="Tu experiencia en Main Store">
            <div class="container benefit-grid">
                <article class="benefit">
                    <span class="benefit-icon" aria-hidden="true">⌕</span>
                    <div>
                        <strong>Explora a tu ritmo</strong>
                        <p>Descubre Main Store cuando quieras.</p>
                    </div>
                </article>
                <article class="benefit">
                    <span class="benefit-icon" aria-hidden="true">♡</span>
                    <div>
                        <strong>Tu espacio personal</strong>
                        <p>Accede a tu cuenta de forma sencilla.</p>
                    </div>
                </article>
                <article class="benefit">
                    <span class="benefit-icon" aria-hidden="true">✦</span>
                    <div>
                        <strong>Siempre bienvenido</strong>
                        <p>Estamos aquí para acompañar tu visita.</p>
                    </div>
                </article>
            </div>
        </section>

        <section class="welcome container">
            <p class="eyebrow">Sigue descubriendo</p>
            <h2>¿Qué te gustaría hacer hoy?</h2>
            <p class="welcome-copy-bottom">
                Continúa explorando Main Store o vuelve a tu espacio cuando quieras.
            </p>
            <a class="button" href="../index.html">Volver a la tienda <span aria-hidden="true">&nbsp;→</span></a>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <span>© 2026 Main Store</span>
            <a class="text-link" href="logout.php">Cerrar sesión</a>
        </div>
    </footer>
</body>
</html>