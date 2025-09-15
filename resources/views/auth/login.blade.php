<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
            max-width: 400px;
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
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
            background-color: #ffe6e6;
            padding: 10px;
            border-radius: 5px;
        }

        .error-message.hidden {
            display: none;
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
            border: none;
            cursor: pointer;
            width: 100%;
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
            <h1>Anmeldung</h1>
        </div>
        <div class="content">
            <h2>Willkommen zurück</h2>
            @if ($errors->any())
                <div class="error-message">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            @if (session('status'))
                <div
                    style="color: green; background-color: #e6ffe6; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                    {{ session('status') }}
                </div>
            @endif
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label for="email">E-Mail</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                        aria-label="E-Mail-Adresse eingeben">
                </div>
                <div class="form-group">
                    <label for="password">Passwort</label>
                    <input type="password" id="password" name="password" required aria-label="Passwort eingeben">
                </div>
                <button type="submit" class="button" aria-label="Anmelden">Anmelden</button>
            </form>
            <p style="margin-top: 15px;">
                <a href="{{ route('password.request') }}">Passwort vergessen?</a>


            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Deine App. Alle Rechte vorbehalten.</p>
            <p>Bei Fragen kontaktiere uns unter <a href="mailto:support@example.com">support@example.com</a>.</p>
        </div>
    </div>
</body>

</html>