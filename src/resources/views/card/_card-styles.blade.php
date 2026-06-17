    <style>
        :root {
            --page-start: {{ $theme['page_start'] }};
            --page-mid: {{ $theme['page_mid'] }};
            --page-end: {{ $theme['page_end'] }};
            --header-start: {{ $theme['header_start'] }};
            --header-end: {{ $theme['header_end'] }};
            --card-surface: {{ $theme['surface'] }};
            --footer-bg: {{ $theme['footer'] }};
            --avatar-start: {{ $theme['avatar_start'] }};
            --avatar-end: {{ $theme['avatar_end'] }};
            --avatar-bg: {{ $theme['avatar_bg'] }};
            --avatar-text: {{ $theme['avatar_text'] }};
            --branch-bg: {{ $theme['branch_bg'] }};
            --branch-text: {{ $theme['branch_text'] }};
            --accent: {{ $theme['accent'] }};
            --accent-dark: {{ $theme['accent_dark'] }};
            --glow: {{ $theme['glow'] }};
            @if($backgroundUrl)
            --banner-image: url('{{ $backgroundUrl }}');
            @else
            --banner-image: linear-gradient(135deg, transparent, transparent);
            @endif
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--page-start);
            color: #1e293b;
        }

        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background: linear-gradient(160deg, var(--page-start) 0%, var(--page-mid) 60%, var(--page-end) 100%);
            position: relative;
            overflow: hidden;
        }

        .page-wrapper::before,
        .page-wrapper::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            opacity: .10;
            pointer-events: none;
        }
        .page-wrapper::before {
            width: 500px; height: 500px;
            background: radial-gradient(circle, var(--glow), transparent);
            top: -150px; right: -150px;
        }
        .page-wrapper::after {
            width: 400px; height: 400px;
            background: radial-gradient(circle, var(--accent), transparent);
            bottom: -100px; left: -100px;
        }

        /* Tarjeta */
        .card {
            width: 100%;
            max-width: 380px;
            background: var(--card-surface);
            border-radius: 24px;
            box-shadow:
                0 25px 60px rgba(0,0,0,.55),
                0 0 0 1px rgba(255,255,255,.08);
            overflow: hidden;
            position: relative;
            z-index: 1;
            animation: fadeUp .5s ease;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Header */
        .card-header {
            background-image:
                linear-gradient(135deg, rgba(15,23,42,.56), rgba(15,23,42,.08)),
                var(--banner-image),
                linear-gradient(135deg, rgba(255,255,255,.10) 0 1px, transparent 1px 18px),
                radial-gradient(circle at 12% 18%, rgba(255,255,255,.22), transparent 26%),
                linear-gradient(135deg, var(--header-start) 0%, var(--header-end) 100%);
            background-size: 100% 100%, cover, 18px 18px, 100% 100%, 100% 100%;
            background-position: center;
            padding: 2rem 1.5rem 4.5rem;
            text-align: center;
            position: relative;
            overflow: visible;
            z-index: 2;
        }
        .card-header::after {
            content: '';
            position: absolute;
            inset: auto -30px -54px -30px;
            height: 96px;
            background: rgba(255,255,255,.14);
            transform: rotate(-4deg);
            pointer-events: none;
            z-index: 0;
        }

        /* Avatar */
        .avatar-wrap {
            position: absolute;
            bottom: -70px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 4;
        }
        .avatar-ring {
            width: 140px; height: 140px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--avatar-start), var(--avatar-end));
            padding: 3px;
        }
        .avatar-inner {
            width: 100%; height: 100%;
            border-radius: 50%;
            overflow: hidden;
            background: var(--avatar-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--avatar-text);
            border: 4px solid #fff;
        }
        .avatar-inner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Body */
        .card-body {
            position: relative;
            z-index: 1;
            padding: 5.75rem 1.75rem 1.75rem;
            text-align: center;
        }

        .emp-name {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            margin-bottom: .35rem;
        }
        .emp-card-type {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: color-mix(in srgb, var(--accent) 12%, #ffffff);
            color: var(--accent-dark);
            border: 1px solid color-mix(in srgb, var(--accent) 22%, #ffffff);
            border-radius: 999px;
            padding: .34rem .8rem;
            font-size: .74rem;
            font-weight: 800;
            letter-spacing: .3px;
            text-transform: uppercase;
            margin-bottom: .75rem;
        }
        .emp-branch {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: var(--branch-bg);
            color: var(--branch-text);
            border-radius: 20px;
            padding: .35rem .9rem;
            font-size: .78rem;
            font-weight: 600;
            margin-bottom: 1.75rem;
        }

        .divider {
            height: 1px;
            background: #f1f5f9;
            margin: 0 -.5rem 1.5rem;
        }

        /* Botones */
        .contact-grid {
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        .contact-btn {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: .9rem 1.25rem;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 600;
            font-size: .9rem;
            transition: all .2s ease;
            cursor: pointer;
            border: none;
        }
        .contact-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,.15);
        }
        .contact-btn:active { transform: translateY(0); }

        .contact-btn .btn-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .contact-btn .btn-label { flex: 1; text-align: left; }
        .contact-btn .btn-arrow { font-size: .75rem; opacity: .5; }

        /* WhatsApp */
        .btn-whatsapp { background: #f0fdf4; color: #15803d; }
        .btn-whatsapp .btn-icon { background: #dcfce7; color: #16a34a; }
        .btn-whatsapp:hover { background: #dcfce7; color: #15803d; }

        /* Instagram */
        .btn-instagram { background: #fff1f2; color: #be123c; }
        .btn-instagram .btn-icon {
            background: linear-gradient(135deg, #f59e0b, #ef4444, #a855f7);
            color: #fff;
        }
        .btn-instagram:hover { background: #ffe4e6; color: #be123c; }

        /* Facebook */
        .btn-facebook { background: #eff6ff; color: #1d4ed8; }
        .btn-facebook .btn-icon { background: #dbeafe; color: #2563eb; }
        .btn-facebook:hover { background: #dbeafe; color: #1d4ed8; }

        /* Sitio web */
        .btn-website { background: var(--branch-bg); color: var(--accent-dark); }
        .btn-website .btn-icon { background: color-mix(in srgb, var(--accent) 28%, #ffffff); color: var(--accent-dark); }
        .btn-website:hover { background: color-mix(in srgb, var(--accent) 18%, #ffffff); color: var(--accent-dark); }

        /* Maps */
        .btn-maps { background: #fff7ed; color: #c2410c; }
        .btn-maps .btn-icon { background: #fed7aa; color: #ea580c; }
        .btn-maps:hover { background: #ffedd5; color: #9a3412; }

        .card-brand-logo {
            display: flex;
            justify-content: center;
            padding-top: 1.2rem;
        }
        .card-brand-logo img {
            display: block;
            width: min(180px, 68%);
            max-height: 54px;
            object-fit: contain;
        }

        /* Footer */
        .card-footer {
            background: var(--footer-bg);
            padding: 1rem 1.75rem;
            text-align: center;
            font-size: .7rem;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
        }
        .card-footer strong { color: #64748b; }
    </style>
