<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar – GIRAG App Vuelos</title>
    <link rel="icon" type="image/png" href="/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            -webkit-font-smoothing: antialiased;
            background: #0f172a;
            position: relative;
            overflow: hidden;
        }
        /* Animated background */
        .bg-pattern {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 10% 90%, rgba(99,102,241,.25) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 90% 10%, rgba(139,92,246,.2) 0%, transparent 60%),
                linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        .bg-dots {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        /* Login card */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        .login-card {
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 20px;
            padding: 40px 36px;
            backdrop-filter: blur(20px);
            box-shadow:
                0 0 0 1px rgba(255,255,255,.04),
                0 24px 80px rgba(0,0,0,.4);
        }
        .login-logo-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 32px;
        }
        .login-logo-ring {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            box-shadow: 0 8px 24px rgba(99,102,241,.4);
        }
        .login-logo-ring img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        .login-app-name {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -.03em;
        }
        .login-app-sub {
            font-size: 12px;
            color: rgba(255,255,255,.35);
            text-transform: uppercase;
            letter-spacing: .1em;
            font-weight: 500;
            margin-top: 2px;
        }

        /* Form */
        .login-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: rgba(255,255,255,.5);
            margin-bottom: 7px;
        }
        .login-input {
            width: 100%;
            display: block;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 10px;
            color: #fff;
            padding: 11px 14px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: all .18s ease;
        }
        .login-input::placeholder { color: rgba(255,255,255,.25); }
        .login-input:focus {
            border-color: #6366f1;
            background: rgba(99,102,241,.08);
            box-shadow: 0 0 0 3px rgba(99,102,241,.2);
        }
        .login-input.is-invalid { border-color: #f43f5e; }
        .login-error {
            font-size: 12px;
            color: #f87171;
            margin-top: 6px;
        }
        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none;
            border-radius: 10px;
            color: white;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 700;
            padding: 13px;
            cursor: pointer;
            transition: all .18s ease;
            letter-spacing: -.01em;
            box-shadow: 0 4px 16px rgba(99,102,241,.35);
        }
        .login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(99,102,241,.45);
            opacity: .95;
        }
        .login-btn:active { transform: translateY(0); }
        .login-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }
        .login-divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,.07);
            margin: 28px 0 24px;
        }
        .form-group { margin-bottom: 18px; }
    </style>
    @livewireStyles
</head>
<body>
    <div class="bg-pattern"></div>
    <div class="bg-dots"></div>

    <div class="login-wrapper">
        {{ $slot }}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>
