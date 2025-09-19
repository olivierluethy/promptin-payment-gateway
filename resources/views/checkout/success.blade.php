@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1>{{ $message }}</h1>
    <p>Ihre Zahlung war erfolgreich.</p>
    <p>Session ID: {{ $session_id }}</p>

    @auth
        <p><a href="{{ route('dashboard') }}">Zu Ihrem Dashboard</a> – hier können Sie Ihre Rechnungen und Abonnements einsehen.</p>
    @else
        <p><a href="{{ route('login') }}">Einloggen</a> oder <a href="{{ route('register') }}">Registrieren</a>, um Ihre Abos zu verwalten.</p>
    @endauth
</div>
@endsection