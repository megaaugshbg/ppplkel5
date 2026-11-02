<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi - Markir.In</title>
</head>
<body>
    <h1>Registrasi Akun</h1>

    @if (session('sukses'))
        <p>{{ session('sukses') }}</p>
    @endif

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="/register">
        @csrf

        <label>Nama</label><br>
        <input type="text" name="nama" value="{{ old('nama') }}"><br><br>

        <label>Email</label><br>
        <input type="email" name="email" value="{{ old('email') }}"><br><br>

        <label>Nomor HP</label><br>
        <input type="text" name="no_hp" value="{{ old('no_hp') }}"><br><br>

        <label>Password</label><br>
        <input type="password" name="password"><br><br>

        <label>Konfirmasi Password</label><br>
        <input type="password" name="password_confirmation"><br><br>

        <button type="submit">Daftar</button>
    </form>
</body>
</html>
