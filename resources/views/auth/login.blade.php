<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk — Panel Admin</title>
    @vite('resources/css/admin.css')
</head>
<body class="login-page">
<form method="post" action="{{ route('admin.login.submit') }}" class="login-box">
    @csrf
    <h1>Panel Admin</h1>
    <p class="muted">{{ config('school.foundation_name') }}</p>

    @error('email')<div class="flash bad">{{ $message }}</div>@enderror

    <label>Email
        <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
    </label>
    <label>Kata sandi
        <input type="password" name="password" required autocomplete="current-password">
    </label>
    <label class="check"><input type="checkbox" name="remember" value="1"> Ingat saya</label>

    <button type="submit" class="btn">Masuk</button>
</form>
</body>
</html>