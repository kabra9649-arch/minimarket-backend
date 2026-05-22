<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (isClienteLoggedIn()) {
    header('Location: catalogo.php');
    exit();
}
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM clientes WHERE email = ? AND activo = 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $cliente = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($cliente && password_verify($password, $cliente['password'])) {
            $upd = $db->prepare("UPDATE clientes SET ultimo_acceso = NOW() WHERE id = ?");
            $upd->bind_param('i', $cliente['id']);
            $upd->execute();
            $upd->close();

            $_SESSION['cliente_id']     = $cliente['id'];
            $_SESSION['cliente_nombre'] = $cliente['nombre'];
            $_SESSION['cliente_email']  = $cliente['email'];
            $_SESSION['portal']         = 'cliente';

            header('Location: catalogo.php');
            exit();
        } else {
            $error = 'Correo o contraseña incorrectos.';
        }
    } else {
        $error = 'Por favor completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Clientes</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --rose:   #C9536A;
            --cream:  #FDF6EC;
            --brown:  #3D1C02;
            --light:  #fff;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            min-height: 100vh;
            display: flex;
        }
        .visual-panel {
            width: 50%;
            position: relative;
            overflow: hidden;
        }
        .visual-panel img {
            width: 100%; height: 100%;
            object-fit: cover;
        }
        .visual-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to right, rgba(201,83,106,0.75), rgba(61,28,2,0.5));
            display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            padding: 60px;
            text-align: center;
        }
        .visual-logo {
            font-family: 'Cormorant Garamond', serif;
            font-size: 3rem;
            font-weight: 700;
            color: #fff;
        }
        .visual-logo span { color: #FDDBB4; }
        .tagline {
            color: rgba(255,255,255,0.75);
            font-size: 0.9rem;
            margin-top: 12px;
            letter-spacing: 1px;
        }
        .form-panel {
            width: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 50px;
            background: var(--cream);
        }
        .form-inner { width: 100%; max-width: 360px; animation: fadeUp 0.5s ease both; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .greeting {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.4rem;
            font-weight: 700;
            color: var(--brown);
            margin-bottom: 6px;
        }
        .greeting-sub { color: #888; font-size: 0.87rem; margin-bottom: 36px; }
        .field-label {
            display: block;
            color: #999;
            font-size: 0.7rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .field-wrap {
            position: relative;
            margin-bottom: 20px;
        }
        .field-wrap i.icon {
            position: absolute;
            left: 16px; top: 50%;
            transform: translateY(-50%);
            color: #bbb;
            font-size: 1rem;
            pointer-events: none;
        }
        .field-wrap input {
            width: 100%;
            background: #fff;
            border: 1.5px solid #E8DDD3;
            color: var(--brown);
            padding: 13px 16px 13px 44px;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.25s, box-shadow 0.25s;
        }
        .field-wrap input:focus {
            border-color: var(--rose);
            box-shadow: 0 0 0 3px rgba(201,83,106,0.12);
        }
        .field-wrap input::placeholder { color: #ccc; }
        .toggle-pass {
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: #bbb; cursor: pointer;
            font-size: 1rem;
            transition: color 0.2s;
        }
        .toggle-pass:hover { color: #888; }
        .btn-login {
            width: 100%;
            background: var(--rose);
            color: #fff;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s, box-shadow 0.3s;
            margin-top: 10px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-login:hover {
            background: #b03f58;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(201,83,106,0.35);
        }
        .btn-login:disabled {
            opacity: 0.7; cursor: not-allowed; transform: none;
        }
        .error-alert {
            background: #fff0f3;
            border: 1px solid #f5c6cb;
            color: #c0392b;
            border-radius: 10px;
            padding: 11px 15px;
            font-size: 0.83rem;
            margin-bottom: 22px;
            display: flex; align-items: center; gap: 8px;
        }
        .links-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 28px;
        }
        .links-row a {
            color: #999;
            font-size: 0.82rem;
            text-decoration: none;
            transition: color 0.2s;
        }
        .links-row a:hover { color: var(--rose); }
        .links-row a.highlight { color: var(--rose); font-weight: 600; }
        .staff-link {
            display: block;
            text-align: center;
            margin-top: 36px;
            color: #bbb;
            font-size: 0.78rem;
            text-decoration: none;
            transition: color 0.2s;
        }
        .staff-link:hover { color: #888; }
        @media (max-width: 768px) {
            .visual-panel { display: none; }
            .form-panel { width: 100%; padding: 40px 24px; }
        }
    </style>
</head>
<body>
    <div class="visual-panel">
        <img src="https://images.unsplash.com/photo-1488477181946-6428a0291777?w=900&q=80" alt="Productos">
        <div class="visual-overlay">
            <div class="visual-logo">NEX<span>SYS</span></div>
            <p class="tagline">Los sabores que amas, a tu alcance</p>
        </div>
    </div>

    <div class="form-panel">
        <div class="form-inner">
            <h1 class="greeting">Bienvenido 👋</h1>
            <p class="greeting-sub">Inicia sesión en tu cuenta de cliente</p>

            <?php if ($error): ?>
            <div class="error-alert">
                <i class="bi bi-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="loginForm" onsubmit="handleSubmit(this)">
                <label class="field-label">Correo electrónico</label>
                <div class="field-wrap">
                    <i class="bi bi-envelope icon"></i>
                    <input type="email" name="email" required placeholder="tu@correo.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <label class="field-label">Contraseña</label>
                <div class="field-wrap">
                    <i class="bi bi-lock icon"></i>
                    <input type="password" name="password" id="passField" required placeholder="••••••••">
                    <button type="button" class="toggle-pass" onclick="togglePass()">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    <i class="bi bi-box-arrow-in-right"></i> Entrar
                </button>
            </form>

            <div class="links-row">
                <a href="#">¿Olvidaste tu contraseña?</a>
                <a href="registro.php" class="highlight">Crear cuenta →</a>
            </div>

            <a href="login.php" class="staff-link">
                <i class="bi bi-shield-lock me-1"></i> Acceso para personal autorizado
            </a>
        </div>
    </div>

    <script>
        function togglePass() {
            const f = document.getElementById('passField');
            const i = document.getElementById('eyeIcon');
            f.type = f.type === 'password' ? 'text' : 'password';
            i.className = f.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        }
        function handleSubmit(form) {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Verificando...';
        }
    </script>
</body>
</html>
