<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — Teman Gudang</title>
    <style>
        :root{font-family:Arial,Helvetica,sans-serif;color:#1d2939;background:#f4f6f8;font-size:16px}
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:20px}
        .card{width:min(100%,440px);background:#fff;border:1px solid #e1e6eb;border-radius:14px;padding:32px;box-shadow:0 12px 32px #123b3210}
        h1{font-size:27px;margin:0 0 8px;color:#123b32}.intro{color:#586579;margin:0 0 24px;line-height:1.5}
        label{display:block;font-weight:700;margin:0 0 7px}input{width:100%;min-height:48px;padding:12px;border:1px solid #b9c4cf;border-radius:7px;font:inherit;margin-bottom:17px}
        input:focus{outline:3px solid #c5e8dc;border-color:#176b56}.button{width:100%;min-height:48px;border:0;border-radius:7px;background:#176b56;color:white;font:inherit;font-weight:700;cursor:pointer}
        .button:hover,.button:focus{background:#10513f;outline:3px solid #b8e4d6}.error{color:#9b271d;margin:-10px 0 15px}
        .notice{padding:12px;border-radius:7px;background:#fff0ed;color:#9b271d;margin-bottom:17px}
        .remember{display:flex;align-items:center;gap:9px;margin:0 0 19px;font-weight:400}.remember input{width:18px;min-height:18px;margin:0}
        .footer{color:#667085;font-size:14px;margin:20px 0 0;text-align:center}
    </style>
</head>
<body>
<main class="card">
    <h1>Masuk ke Teman Gudang</h1>
    <p class="intro">Silakan masuk dengan akun petugas gudang untuk mengelola peminjaman alat.</p>

    @if ($errors->any())
        <div class="notice" role="alert">Email atau kata sandi tidak cocok, atau data belum lengkap.</div>
    @endif

    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>

        <label for="password">Kata sandi</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>

        <label class="remember" for="remember">
            <input id="remember" name="remember" type="checkbox" value="1">
            Ingat saya di komputer ini
        </label>

        <button class="button" type="submit">Masuk</button>
    </form>

    <p class="footer">Akun hanya dapat dibuat oleh administrator sistem.</p>
</main>
</body>
</html>
