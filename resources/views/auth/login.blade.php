<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
    <main class="login-card">
        <x-signal.brand />
        <h1>Sign in</h1>
        <p>Access your infrastructure workspace.</p>
        <form action="{{ route('login.store') }}" method="POST">
            @csrf
            <label for="login-email">Email address</label>
            <input class="form-control" id="login-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" @error('email') aria-describedby="email-error" aria-invalid="true" @enderror>
            @error('email') <p class="form-error" id="email-error" role="alert">{{ $message }}</p> @enderror
            <label for="login-password">Password</label>
            <input class="form-control" id="login-password" type="password" name="password" required maxlength="255" autocomplete="current-password" @error('password') aria-describedby="password-error" aria-invalid="true" @enderror>
            @error('password') <p class="form-error" id="password-error" role="alert">{{ $message }}</p> @enderror
            <button class="primary-button" type="submit">Sign in</button>
        </form>
    </main>
</body>
</html>
