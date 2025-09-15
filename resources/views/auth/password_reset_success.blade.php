<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passwortänderung erfolgreich</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        .container {
            max-width: 500px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            text-align: center;
        }
        .header {
            background-color: #4CAF50;
            color: #ffffff;
            padding: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: normal;
        }
        .content {
            padding: 30px;
        }
        .content h2 {
            color: #4CAF50;
            font-size: 22px;
            margin-bottom: 15px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4CAF50;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #45a049;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 15px;
            font-size: 14px;
            color: #777;
        }
        .footer a {
            color: #4CAF50;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .container {
                margin: 20px;
            }
            .content {
                padding: 20px;
            }
            .header h1 {
                font-size: 20px;
            }
            .content h2 {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Password successfully changed</h1>
        </div>
        <div class="content">
            @if (session('error'))
                <div style="color: red; padding: 10px; background-color: #ffe6e6; margin-bottom: 15px;">
                    {{ session('error') }}
                </div>
            @endif
            <h2>Hallo {{ $user->name ?? 'Benutzer' }},</h2>
            <p>Dein Passwort wurde erfolgreich geändert. Du kannst dich jetzt mit deinem neuen Passwort anmelden.</p>
            <a href="{{ url('/login') }}" class="button" aria-label="Zum Login-Bereich navigieren">Zum Login</a>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} PromptIn. Alle Rechte vorbehalten.</p>
            <p>Bei Fragen kontaktiere uns unter <a href="mailto:business.promptin@gmail.com">business.promptin@gmail.com</a>.</p>
        </div>
    </div>
</body>
</html>