<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Registrierung – PromptIn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #4CAF50 0%, #81C784 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Arial', sans-serif;
            color: #333;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
        }

        .card-header {
            background-color: #4CAF50;
            color: #fff;
            text-align: center;
            padding: 1.5rem;
        }

        .card-header h1 {
            font-size: 1.75rem;
            margin: 0;
        }

        .nav-tabs {
            border: none;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .nav-tabs .nav-link {
            border: none;
            border-radius: 30px;
            padding: 0.6rem 1.5rem;
            font-weight: bold;
            color: #4CAF50;
        }

        .nav-tabs .nav-link.active {
            background-color: #4CAF50;
            color: #fff;
            box-shadow: 0 4px 10px rgba(76, 175, 80, 0.3);
        }

        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 6px rgba(76, 175, 80, 0.4);
        }

        .btn-success {
            background-color: #4CAF50;
            border-color: #4CAF50;
            font-weight: bold;
        }

        .btn-success:hover {
            background-color: #45a049;
        }

        .error-message {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: left;
        }

        .footer {
            text-align: center;
            font-size: 0.9rem;
            color: #777;
            margin-top: 1rem;
        }

        .footer a {
            color: #4CAF50;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="card animate__animated animate__fadeInDown">
        <div class="card-header">
            <h1>PromptIn</h1>
        </div>
        <div class="card-body p-4">

            {{-- Tabs für Login & Register --}}
            <ul class="nav nav-tabs" id="authTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login"
                        type="button" role="tab">Login</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register"
                        type="button" role="tab">Registrieren</button>
                </li>
            </ul>

            <div class="tab-content">
                {{-- LOGIN --}}
                <div class="tab-pane fade show active" id="login" role="tabpanel">
                    <h5 class="text-center mb-3 text-success">Willkommen zurück 👋</h5>

                    @if ($errors->any())
                        <div class="error-message">
                            @foreach ($errors->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="alert alert-success text-center">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">E-Mail</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control"
                                required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">Passwort</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Anmelden</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('password.request') }}">Passwort vergessen?</a>
                    </div>
                </div>

                {{-- REGISTER --}}
                <div class="tab-pane fade" id="register" role="tabpanel">
                    <h5 class="text-center mb-3 text-success">Konto erstellen ✨</h5>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="email_reg" class="form-label fw-bold">E-Mail</label>
                            <input type="email" id="email_reg" name="email" value="{{ old('email') }}"
                                class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_reg" class="form-label fw-bold">Passwort</label>
                            <input type="password" id="password_reg" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label fw-bold">Passwort bestätigen</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Registrieren</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="footer p-3">
            <p>&copy; {{ date('Y') }} PromptIn. Alle Rechte vorbehalten.</p>
            <p>Fragen? <a href="mailto:business.promptin@gmail.com">business.promptin@gmail.com</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>