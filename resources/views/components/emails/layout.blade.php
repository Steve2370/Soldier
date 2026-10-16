@props(['sujet' => 'Soldier', 'preheader' => null])
@php
    $font = "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif";
    $mono = "'DM Mono', 'SFMono-Regular', Menlo, Consolas, 'Courier New', monospace";
    $asset = fn (string $file) => rtrim(config('app.url', 'https://soldierkey.com'), '/') . '/assets/email/' . $file;
@endphp
<!DOCTYPE html>
<html lang="fr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="dark">
    <meta name="supported-color-schemes" content="dark">
    <title>{{ $sujet }}</title>
    {{-- Polices chargées seulement par les clients qui l'acceptent (Apple Mail, iOS) ; repli système ailleurs. --}}
    <link href="https://fonts.googleapis.com/css2?family=Audiowide&family=DM+Mono&display=swap" rel="stylesheet">
    <style>
        body { margin: 0 !important; padding: 0 !important; background: #02050a; }
        a { color: #4fc3f7; }
        @media only screen and (max-width: 620px) {
            .s-container { width: 100% !important; }
            .s-pad { padding-left: 22px !important; padding-right: 22px !important; }
            .s-title { font-size: 22px !important; }
            .s-code { font-size: 32px !important; letter-spacing: 8px !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#02050a;">
@if($preheader)
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px; color:#02050a;">{{ $preheader }}&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;</div>
@endif
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#02050a" style="background-color:#02050a;">
    <tr>
        <td align="center" style="padding:32px 12px 40px;">
            <table role="presentation" class="s-container" width="600" cellspacing="0" cellpadding="0" border="0" style="width:600px; max-width:600px;">

                {{-- En-tête --}}
                <tr>
                    <td style="border-radius:18px 18px 0 0; overflow:hidden; border:1px solid #1a2b3c; border-bottom:0;">
                        <a href="https://soldierkey.com" style="text-decoration:none;">
                            <img src="{{ $asset('header.jpg') }}" width="600" alt="Soldier — Gestionnaire de mots de passe chiffré" style="display:block; width:100%; max-width:600px; height:auto; border:0; border-radius:18px 18px 0 0; color:#eef3f8; font-family:{{ $font }}; font-size:20px;">
                        </a>
                    </td>
                </tr>
                <tr>
                    <td height="3" bgcolor="#3df5c0" style="height:3px; line-height:3px; font-size:3px; background-color:#3df5c0; background-image:linear-gradient(90deg, #2d9fd4, #3df5c0 50%, #2d9fd4);">&nbsp;</td>
                </tr>

                {{-- Carte --}}
                <tr>
                    <td class="s-pad" bgcolor="#07121c" style="background-color:#07121c; border:1px solid #1a2b3c; border-top:0; border-radius:0 0 18px 18px; padding:36px 40px 34px; font-family:{{ $font }}; font-size:15px; line-height:1.7; color:#c3cedb;">
                        {{ $slot }}
                    </td>
                </tr>

                {{-- Pied --}}
                <tr>
                    <td align="center" style="padding:26px 20px 0; font-family:{{ $mono }}; font-size:10px; letter-spacing:2px; color:#4fc3f7;">
                        ARGON2ID &nbsp;◆&nbsp; AES-256-GCM &nbsp;◆&nbsp; RSA-4096
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:14px 24px 0; font-family:{{ $font }}; font-size:12px; line-height:1.8; color:#6e8aa3;">
                        Cet e-mail a été envoyé par <strong style="color:#8fa2b8;">Soldier</strong> · <a href="https://soldierkey.com" style="color:#4fc3f7; text-decoration:none;">soldierkey.com</a><br>
                        Vous n’êtes pas à l’origine de cette action ? <a href="mailto:support@soldierkey.com" style="color:#4fc3f7; text-decoration:none;">Contactez le support</a>.<br>
                        Soldier ne vous demandera jamais votre Master Password par e-mail.<br>
                        <span style="color:#3a5168;">© {{ date('Y') }} Soldier</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
