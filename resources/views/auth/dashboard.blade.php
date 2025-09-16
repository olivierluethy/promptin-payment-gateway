<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benutzer-Dashboard</title>
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
        .subscription-card {
            transition: transform 0.3s ease;
        }
        .subscription-card:hover {
            transform: scale(1.05);
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#plans">Pläne</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#stats">Statistiken</a>
                    </li>
                </ul>
                <div class="dropdown">
                    <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ $user->name ?? 'Benutzer' }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end animate__animated animate__fadeIn">
                        <li class="dropdown-item">
                            <strong>{{ $user->name ?? 'Nicht angegeben' }}</strong><br>
                            <small class="text-muted">{{ $user->email }}</small>
                        </li>
                        <li><a class="dropdown-item" href="{{ url('/settings') }}">Einstellungen</a></li>
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
            Willkommen im Dashboard, {{ $user->name ?? 'Benutzer' }}
        </h2>

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($plan)
            <div class="alert alert-success text-center mb-4 animate__animated animate__fadeIn">
                Aktiver Plan: {{ $plan->name ?? 'Unbekannt' }} ({{ $plan->duration ?? '1' }} Monate)
            </div>
        @else
            <div class="alert alert-warning text-center mb-4 animate__animated animate__pulse">
                Du hast derzeit kein aktives Abonnement. Entdecke unsere Pläne oder nutze den kostenlosen Plan!
            </div>
        @endif

        <div id="plans" class="row g-4 mb-5">
            <div class="col-lg-3 col-md-6">
                <div class="card subscription-card h-100 animate__animated animate__fadeInUp">
                    <div class="card-body">
                        <h3 class="card-title fw-bold">Free</h3>
                        <p class="card-text fs-2 text-success">Kostenlos</p>
                        <p class="text-muted">Für immer kostenlos</p>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item border-0">Basic API-Zugriff</li>
                            <li class="list-group-item border-0">1 Projekt</li>
                            <li class="list-group-item border-0">100 MB Speicher</li>
                            <li class="list-group-item border-0">Community-Support</li>
                        </ul>
                        <button class="btn btn-outline-success w-100" disabled>Aktiviert</button>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card subscription-card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                    <div class="card-body">
                        <h3 class="card-title fw-bold">Basic</h3>
                        <p class="card-text fs-2 text-success">€{{ number_format(1, 2) }}</p>
                        <p class="text-muted">pro Monat</p>
                        <select class="form-select mb-3" aria-label="Dauer auswählen">
                            <option value="1">1 Monat (€1)</option>
                            <option value="12">12 Monate (€12)</option>
                        </select>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item border-0">Erweiterter API-Zugriff</li>
                            <li class="list-group-item border-0">5 Projekte</li>
                            <li class="list-group-item border-0">1 GB Speicher</li>
                            <li class="list-group-item border-0">E-Mail-Support</li>
                        </ul>
                        <a href="{{ url('/checkout/1') }}" class="btn btn-success w-100">Abonnieren</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card subscription-card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                    <div class="card-body">
                        <h3 class="card-title fw-bold">Pro</h3>
                        <p class="card-text fs-2 text-success">€{{ number_format(5, 2) }}</p>
                        <p class="text-muted">pro Monat</p>
                        <select class="form-select mb-3" aria-label="Dauer auswählen">
                            <option value="1">1 Monat (€5)</option>
                            <option value="12">12 Monate (€60)</option>
                        </select>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item border-0">Unbegrenzter API-Zugriff</li>
                            <li class="list-group-item border-0">20 Projekte</li>
                            <li class="list-group-item border-0">10 GB Speicher</li>
                            <li class="list-group-item border-0">Priorisierter Support</li>
                        </ul>
                        <a href="{{ url('/checkout/2') }}" class="btn btn-success w-100">Abonnieren</a>
                    </div>
                </div>
            </div>
        </div>

        <div id="stats" class="mb-5">
            <h2 class="fw-bold mb-4">Nutzungsstatistiken</h2>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card shadow-sm text-center animate__animated animate__slideInUp">
                        <div class="card-body">
                            <p class="card-text text-muted">API-Aufrufe</p>
                            <p class="card-title fs-5 text-success">{{ $stats->apiCalls ?? '1200' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm text-center animate__animated animate__slideInUp" style="animation-delay: 0.1s;">
                        <div class="card-body">
                            <p class="card-text text-muted">Aktive Projekte</p>
                            <p class="card-title fs-5 text-success">{{ $stats->activeProjects ?? '5' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm text-center animate__animated animate__slideInUp" style="animation-delay: 0.2s;">
                        <div class="card-body">
                            <p class="card-text text-muted">Speicher genutzt</p>
                            <p class="card-title fs-5 text-success">{{ $stats->storageUsed ?? '2.3 GB' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm text-center animate__animated animate__slideInUp" style="animation-delay: 0.3s;">
                        <div class="card-body">
                            <p class="card-text text-muted">Letzter Login</p>
                            <p class="card-title fs-5 text-success">{{ $stats->lastLogin ?? '2025-09-15' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Benutzerinformationen</div>
            <div class="card animate__animated animate__fadeIn">
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $user->name ?? 'Nicht angegeben' }}</p>
                    <p><strong>E-Mail:</strong> {{ $user->email }}</p>
                    <p><strong>Registriert seit:</strong> {{ $user->created_at->format('d.m.Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer py-4 text-center">
        <p>&copy; {{ date('Y') }} PromptIn. Alle Rechte vorbehalten.</p>
        <p>Bei Fragen kontaktiere uns unter <a href="mailto:business.promptin@gmail.com">business.promptin@gmail.com</a>.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>