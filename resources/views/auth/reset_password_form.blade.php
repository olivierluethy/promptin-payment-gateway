<!DOCTYPE html>
<html>
<head>
    <title>Passwort zurücksetzen</title>
</head>
<body>
    <h1>Passwort zurücksetzen</h1>

    @if (session('error'))
        <div style="color: red;">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ url('/reset-password/confirm') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <label>Neues Passwort:</label>
        <input type="password" name="password" required>
        <br>

        <label>Passwort bestätigen:</label>
        <input type="password" name="password_confirmation" required>
        <br>

        <button type="submit">Passwort ändern</button>
    </form>
</body>
</html>