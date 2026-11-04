<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>API Health - SWMS</title>
</head>
<body style="font-family: system-ui, sans-serif; max-width: 720px; margin: 4rem auto; padding: 0 1rem; color: #17392d">
    <header style="display: flex; justify-content: space-between; align-items: center">
        <h1>SWMS API Health</h1>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </header>
    <p>Dashboard ini hanya menampilkan status layanan; pengelolaan aplikasi dilakukan melalui API.</p>
    <dl>
        <dt>API health endpoint</dt>
        <dd><a href="{{ $apiHealthUrl }}">{{ $apiHealthUrl }}</a> — aplikasi aktif</dd>
        <dt>Database</dt>
        <dd>{{ $databaseStatus === 'healthy' ? 'Healthy' : 'Unhealthy' }}</dd>
    </dl>
</body>
</html>
