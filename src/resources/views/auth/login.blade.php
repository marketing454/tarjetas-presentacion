<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Team Presentation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a2e00 50%, #0a0a0a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0,0,0,.5);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #111111, #1a2e00);
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .login-header img {
            height: 52px;
            width: auto;
            object-fit: contain;
            margin-bottom: 1rem;
            filter: brightness(0) invert(1);
        }
        .login-header h1 { font-size: 1.4rem; font-weight: 700; color: #fff; margin: 0; }
        .login-header p  { color: rgba(255,255,255,.55); font-size: .85rem; margin: .25rem 0 0; }

        .login-body { padding: 2rem; }

        .form-label { font-size: .85rem; font-weight: 600; color: #374151; }
        .form-control {
            border-radius: 10px;
            border: 1.5px solid #e5e7eb;
            padding: .65rem 1rem;
            font-size: .9rem;
            transition: border-color .2s;
        }
        .form-control:focus {
            border-color: #8dc63f;
            box-shadow: 0 0 0 3px rgba(141,198,63,.15);
        }
        .input-group-text {
            border-radius: 10px 0 0 10px;
            background: #f9fafb;
            border: 1.5px solid #e5e7eb;
            color: #9ca3af;
        }
        .input-group .form-control { border-radius: 0 10px 10px 0; }

        .btn-login {
            background: linear-gradient(135deg, #8dc63f, #76b52e);
            border: none;
            border-radius: 10px;
            padding: .75rem;
            font-weight: 600;
            font-size: .95rem;
            color: #111;
            width: 100%;
            transition: all .2s;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(141,198,63,.35);
            color: #111;
        }

        .login-footer {
            text-align: center;
            padding: 1rem 2rem 1.5rem;
            font-size: .75rem;
            color: #9ca3af;
        }

        /* Override Bootstrap blues */
        .form-check-input:checked { background-color: #8dc63f; border-color: #8dc63f; }
        .form-check-input:focus { border-color: #8dc63f; box-shadow: 0 0 0 .25rem rgba(141,198,63,.25); }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <img src="{{ asset('Logo-compulago-corporativo.png') }}" alt="COMPULAGO">
            <h1>Team Presentation</h1>
            <p>Panel de administración · COMPULAGO</p>
        </div>

        <div class="login-body">
            @if($errors->any())
                <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 py-2 mb-3" role="alert">
                    <i class="fas fa-circle-exclamation"></i>
                    <small>{{ $errors->first() }}</small>
                </div>
            @endif

            <form method="POST" action="{{ route('auth.login') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope fa-sm"></i></span>
                        <input id="email" type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required autofocus
                               placeholder="admin@compulago.com">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock fa-sm"></i></span>
                        <input id="password" type="password" name="password"
                               class="form-control" required placeholder="••••••••">
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember" style="font-size:.85rem;color:#6b7280;">
                            Recordarme
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="fas fa-right-to-bracket me-2"></i> Iniciar sesión
                </button>
            </form>
        </div>

        <div class="login-footer">
            <i class="fas fa-shield-halved me-1"></i> Acceso restringido solo para administradores
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
