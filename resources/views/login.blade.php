<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Masuk - Markir.In</title>
</head>
<body>
    <h1>Masuk</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="/login">
        @csrf

        <label>Email</label><br>
        <input type="email" name="email" value="{{ old('email') }}"><br><br>

        <label>Password</label><br>
        <input type="password" name="password"><br><br>

        <label><input type="checkbox" name="remember" value="1"> Ingat saya</label><br><br>

        <button type="submit">Masuk</button>
    </form>

    <p>Belum punya akun? <a href="/register">Daftar</a></p>
</body>
</html>
