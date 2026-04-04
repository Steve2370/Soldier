<!DOCTYPE html>
<html lang="fr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $sujet ?? 'Soldier' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background-color: #0a0a0a;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            color: #e0e0e0;
            -webkit-font-smoothing: antialiased;
        }
        .email-wrapper {
            background-color: #0a0a0a;
            padding: 40px 20px;
        }
        .email-container {
            max-width: 580px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            padding-bottom: 32px;
        }
        .header img {
            width: 72px;
            height: 72px;
            border-radius: 50%;
        }
        .header-brand {
            margin-top: 14px;
            font-size: 1.4rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.02em;
        }
        .header-tagline {
            margin-top: 4px;
            font-size: 0.7rem;
            color: #404040;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .card {
            background: #141414;
            border: 1px solid #2a2a2a;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .card-accent {
            height: 3px;
            background: linear-gradient(90deg, #217eaa, #2d9fd4, #217eaa);
        }
        .card-body {
            padding: 32px;
        }
        .icon-wrap {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .icon-success { background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.25); }
        .icon-warning { background: rgba(245,158,11,0.12); border: 1px solid rgba(245,158,11,0.25); }
        .icon-danger { background: rgba(239,68,68,0.12);  border: 1px solid rgba(239,68,68,0.25); }
        .icon-info { background: rgba(45,159,212,0.12); border: 1px solid rgba(45,159,212,0.25); }
        h1 {
            font-size: 1.375rem;
            font-weight: 800;
            color: #ffffff;
            text-align: center;
            margin-bottom: 10px;
            line-height: 1.3;
        }
        h2 {
            font-size: 1rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
        }
        p {
            font-size: 0.9rem;
            color: #a0a0a0;
            line-height: 1.75;
            text-align: center;
            margin-bottom: 20px;
        }
        p.left { text-align: left; }
        strong { color: #e0e0e0; }
        /* Panel */
        .panel {
            background: #0d0d0d;
            border: 1px solid #2a2a2a;
            border-left: 3px solid #217eaa;
            border-radius: 10px;
            padding: 18px 20px;
            margin: 20px 0;
            text-align: left;
        }
        .panel p { text-align: left; margin: 0; }
        .panel ul {
            list-style: none;
            padding: 0;
            margin: 8px 0 0;
        }
        .panel ul li {
            font-size: 0.85rem;
            color: #808080;
            padding: 3px 0;
            padding-left: 16px;
            position: relative;
        }
        .panel ul li::before {
            content: '→';
            position: absolute;
            left: 0;
            color: #217eaa;
        }
        .panel ul li strong { color: #c0c0c0; }
        .code-box {
            background: #0a0a0a;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            margin: 24px 0;
        }
        .code-digits {
            font-size: 2.8rem;
            font-weight: 800;
            color: #2d9fd4;
            letter-spacing: 0.5em;
            font-family: 'Courier New', monospace;
            padding-left: 0.5em;
        }
        .code-expire {
            font-size: 0.78rem;
            color: #505050;
            margin-top: 10px;
        }
        .btn-wrap {
            text-align: center;
            margin: 24px 0;
        }
        .btn {
            display: inline-block;
            padding: 14px 36px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            letter-spacing: 0.01em;
        }
        .btn-primary {
            background: linear-gradient(135deg, #217eaa, #2d9fd4);
            color: #ffffff !important;
        }
        .btn-danger {
            background: rgba(239,68,68,0.12);
            color: #ef4444 !important;
            border: 1px solid rgba(239,68,68,0.3);
        }
        .warning-box {
            background: rgba(245,158,11,0.08);
            border: 1px solid rgba(245,158,11,0.2);
            border-radius: 10px;
            padding: 14px 18px;
            margin: 20px 0;
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }
        .warning-box p {
            text-align: left;
            margin: 0;
            font-size: 0.82rem;
            color: #808080;
        }
        .divider {
            height: 1px;
            background: #1e1e1e;
            margin: 24px 0;
        }
        .footer {
            text-align: center;
            padding: 0 20px 20px;
        }
        .footer p {
            font-size: 0.75rem;
            color: #383838;
            line-height: 1.9;
            margin: 0;
        }
        .footer a {
            color: #217eaa;
            text-decoration: none;
        }
        .security-badges {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .badge {
            display: inline-block;
            background: rgba(33,126,170,0.1);
            border: 1px solid rgba(45,159,212,0.2);
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 0.68rem;
            color: #2d9fd4;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>
<div class="email-wrapper">
    <div class="email-container">

        <div class="header">
            <img src="https://soldierkey.com/assets/Soldier-Logo.png" alt="Soldier" width="72" height="72">
            <div class="header-brand">Soldier</div>
            <div class="header-tagline">Gestionnaire de mots de passe · Zero-knowledge</div>
        </div>

        <div class="card">
            <div class="card-accent"></div>
            <div class="card-body">
                {{ $slot }}
            </div>
        </div>

        <div class="footer">
            <div class="security-badges">
                <span class="badge">AES-256-GCM</span>
                <span class="badge">ARGON2ID</span>
                <span class="badge">RSA-4096</span>
                <span class="badge">ZERO-KNOWLEDGE</span>
            </div>
            <p>
                Cet email a été envoyé par <strong style="color:#505050;">Soldier</strong> — <a href="https://soldierkey.com">soldierkey.com</a><br>
                Si vous n'êtes pas à l'origine de cette action, <a href="mailto:support@soldierkey.com">contactez notre support</a>.<br>
                <span style="color:#2a2a2a;">© 2026 Soldier · soldierkey.com</span>
            </p>
        </div>

    </div>
</div>
</body>
</html>
