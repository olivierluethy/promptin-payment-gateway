<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benutzer-Dashboard</title>
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
            max-width: 800px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 20px;
        }

        .header {
            background-color: #4CAF50;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: normal;
        }

        .logout-button {
            padding: 8px 16px;
            background-color: #ff4444;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .logout-button:hover {
            background-color: #cc0000;
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
            margin-bottom: 10px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .card {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
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
            text-align: center;
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
                margin: 10px;
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

            .card {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Willkommen im Dashboard, {{ $user->name ?? 'Benutzer' }}</h1>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="logout-button" aria-label="Abmelden">Abmelden</button>
            </form>

        </div>
        <div class="content">
            @if (session('error'))
                <div style="color: red; padding: 10px; background-color: #ffe6e6; margin-bottom: 15px;">
                    {{ session('error') }}
                </div>
            @endif
            @if (session('status'))
                <div style="color: green; padding: 10px; background-color: #e6ffe6; margin-bottom: 15px;">
                    {{ session('status') }}
                </div>
            @endif

            <div class="section">
                <div class="section-title">Benutzerinformationen</div>
                <div class="card">
                    <p><strong>Name:</strong> {{ $user->name ?? 'Nicht angegeben' }}</p>
                    <p><strong>E-Mail:</strong> {{ $user->email }}</p>
                    <p><strong>Registriert seit:</strong> {{ $user->created_at->format('d.m.Y') }}</p>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Abonnement</div>
                <div class="card">
                    @if ($plan)
                        <p><strong>Plan:</strong> {{ $plan->name ?? 'Unbekannt' }}</p>
                        <p><strong>Preis:</strong> {{ number_format($plan->price ?? 0, 2) }} €/Monat</p>
                        <p><strong>Status:</strong> Aktiv</p>
                        <a href="{{ url('/checkout/' . ($plan->id ?? 1)) }}" class="button"
                            aria-label="Abonnement ändern">Abonnement ändern</a>
                    @else
                        <p>Du hast derzeit kein aktives Abonnement.</p>
                        <a href="{{ url('/checkout/1') }}" class="button" aria-label="Abonnement starten">Abonnement
                            starten</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Deine App. Alle Rechte vorbehalten.</p>
            <p>Bei Fragen kontaktiere uns unter <a href="mailto:support@example.com">support@example.com</a>.</p>
        </div>
    </div>
</body>

</html>