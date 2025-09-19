<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Einstellungen - PromptIn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
        }

        .navbar-brand {
            font-size: 1.75rem;
            font-weight: bold;
        }

        .dropdown-menu {
            min-width: 200px;
        }

        .card {
            border-radius: 10px;
        }

        .btn-success {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }

        .btn-success:hover {
            background-color: #45a049;
        }

        .btn-danger {
            background-color: #ff4444;
        }

        .btn-danger:hover {
            background-color: #cc0000;
        }

        .footer {
            background-color: #f9f9f9;
            color: #777;
        }

        .footer a {
            color: #4CAF50;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .form-section {
            max-width: 600px;
            margin: 0 auto;
        }

        .alert {
            max-width: 600px;
            margin: 0 auto 1rem;
        }

        @media (max-width: 576px) {
            .container {
                padding: 15px;
            }

            .navbar-brand {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top shadow">
        <div class="container">
            <a class="navbar-brand" href="#">PromptIn</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}#plans">Pläne</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}#stats">Statistiken</a>
                    </li>
                </ul>
                <div class="dropdown">
                    <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        {{ $user->name ?? 'Benutzer' }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end animate__animated animate__fadeIn">
                        <li class="dropdown-item">
                            <strong>{{ $user->name ?? 'Nicht angegeben' }}</strong><br>
                            <small class="text-muted">{{ $user->email }}</small>
                        </li>
                        <li><a class="dropdown-item active" href="{{ route('settings') }}">Einstellungen</a></li>
                        <li>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">Abmelden</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="text-center fw-bold mb-4 animate__animated animate__fadeIn">
            Einstellungen
        </h2>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="form-section mb-5">
            <div class="card shadow-sm animate__animated animate__fadeInUp">
                <div class="card-body">
                    <h3 class="card-title fw-bold mb-4">Profil bearbeiten</h3>
                    <form id="profile-form" action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Benutzername</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $user->name ?? '' }}"
                                required maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-Mail-Adresse</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}"
                                required>
                            <small class="form-text text-muted">Nach dem Ändern der E-Mail-Adresse erhältst du eine Bestätigungs-E-Mail.</small>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Änderungen speichern</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="form-section mb-5">
            <div class="card shadow-sm animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                <div class="card-body">
                    <h3 class="card-title fw-bold mb-4">Passwort ändern</h3>
                    <form id="password-form" action="{{ route('settings.password') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Aktuelles Passwort</label>
                            <input type="password" class="form-control" id="current_password" name="current_password"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Neues Passwort</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Neues Passwort bestätigen</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Passwort ändern</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="card shadow-sm animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                <div class="card-body">
                    <h3 class="card-title fw-bold mb-4">Konto löschen</h3>
                    <p class="text-muted mb-3">
                        Das Löschen deines Kontos ist permanent und kann nicht rückgängig gemacht werden. Alle deine Daten, einschließlich Abonnements und Projekte, werden gelöscht.
                    </p>
                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal"
                        data-bs-target="#deleteAccountModal">Konto löschen</button>
                </div>
            </div>
        </div>

        <!-- Delete Account Confirmation Modal -->
        <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteAccountModalLabel">Konto löschen bestätigen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Bist du sicher, dass du dein Konto löschen möchtest? Diese Aktion kann nicht rückgängig gemacht werden.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <form action="{{ route('settings.delete') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Konto löschen</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer py-4 text-center">
        <p>&copy; {{ date('Y') }} PromptIn. Alle Rechte vorbehalten.</p>
        <p>Bei Fragen kontaktiere uns unter <a
                href="mailto:business.promptin@gmail.com">business.promptin@gmail.com</a>.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>