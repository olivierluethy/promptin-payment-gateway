<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passwort zurücksetzen</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #4CAF50, #81C784);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        .container {
            max-width: 420px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: fadeIn 0.6s ease-in-out;
        }

        .header {
            background-color: #4CAF50;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: normal;
            animation: slideDown 0.8s ease-in-out;
        }

        .content {
            padding: 30px;
            text-align: center;
        }

        .content h2 {
            color: #4CAF50;
            font-size: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 6px rgba(76, 175, 80, 0.3);
        }

        .button {
            display: inline-block;
            padding: 14px;
            background-color: #4CAF50;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s, transform 0.2s;
        }

        .button:hover {
            background-color: #45a049;
            transform: scale(1.02);
        }

        .status-message {
            font-size: 15px;
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 6px;
            animation: fadeIn 0.6s ease-in-out;
        }

        .status-success {
            background-color: #e6ffe6;
            color: #2e7d32;
            border: 1px solid #81c784;
        }

        .status-error {
            background-color: #ffe6e6;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        .info-text {
            font-size: 14px;
            margin-top: 15px;
            color: #555;
        }

        .footer {
            background-color: #f9f9f9;
            padding: 15px;
            font-size: 14px;
            color: #777;
            text-align: center;
        }

        .footer a {
            color: #4CAF50;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media (max-width: 600px) {
            .container {
                margin: 20px;
            }

            .content {
                padding: 20px;
            }

            .header h1 {
                font-size: 18px;
            }

            .content h2 {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Passwort zurücksetzen</h1>
        </div>
        <div class="content">
            <h2>Bitte gib deine E-Mail ein</h2>

            {{-- Status-Nachrichten --}}
            @if (session('status'))
                <div class="status-message status-success">
                    {{ session('status') }}<br>
                    <strong>Tipp:</strong> Prüfe auch deinen <em>Spam-Ordner</em>, falls du keine Mail findest.
                </div>
            @endif

            @if ($errors->any())
                <div class="status-message status-error">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Formular --}}
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-group">
                    <label for="email">E-Mail-Adresse</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        placeholder="z. B. name@example.com">
                </div>
                <button type="submit" class="button">Link zusenden</button>
            </form>

            <p class="info-text">
                Nach Absenden erhältst du eine E-Mail mit einem Link zum Zurücksetzen deines Passworts.
            </p>

            <p style="margin-top: 15px;">
                <a href="{{ route('login') }}">Zurück zum Login</a>
            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} PromptIn. Alle Rechte vorbehalten.</p>
            <p>Support: <a href="mailto:business.promptin@gmail.com">business.promptin@gmail.com</a></p>
        </div>
    </div>
</body>

</html>