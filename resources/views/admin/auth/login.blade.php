<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Administrator - SWMS</title>
    <style>
        body { background: #e8eee7; color: #17392d; font-family: system-ui, sans-serif; margin: 0; }
        main { align-items: center; display: flex; justify-content: center; min-height: 100vh; padding: 1rem; }
        section { background: #f8fbf6; border: 1px solid #cbd8c8; border-radius: 1rem; max-width: 26rem; padding: 2rem; width: 100%; }
        h1 { margin-top: 0; }
        label { display: block; font-weight: 600; margin-top: 1rem; }
        input { border: 1px solid #b9cbb8; border-radius: .5rem; box-sizing: border-box; margin-top: .5rem; padding: .8rem; width: 100%; }
        button { background: #244c38; border: 0; border-radius: .5rem; color: white; cursor: pointer; margin-top: 1.5rem; padding: .8rem; width: 100%; }
        .message { background: #fff0ef; border: 1px solid #e8b8b2; border-radius: .5rem; margin-bottom: 1rem; padding: .8rem; }
    </style>
</head>
<body>
<main>
    <section>
        <p>SWMS · Backend API</p>
        <h1>Masuk administrator</h1>
        <p>Dashboard ini hanya menampilkan health check layanan API.</p>

        @if ($errors->any())
            <div class="message" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <label for="email">Email administrator</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">

            <label for="password">Kata sandi</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">

            <label><input type="checkbox" name="remember" value="1" style="width:auto"> Ingat saya</label>
            <button type="submit">Masuk</button>
        </form>
    </section>
</main>
</body>
</html>
