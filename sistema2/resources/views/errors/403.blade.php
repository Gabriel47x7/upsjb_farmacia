<!doctype html>
<html lang="es" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google" content="notranslate">
    <meta name="theme-color" content="#ff0a41">
    <title>Acceso denegado | FARM SJB</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 p-3">
    <main class="card border-0 shadow-sm text-center w-100" style="max-width: 460px">
        <div class="card-body p-5">
            <h1 class="text-danger fw-bold mb-3">Acceso denegado</h1>
            <p class="text-secondary mb-4">Tu rol no tiene permiso para abrir esta página.</p>
            <a href="{{ route('dashboard.index') }}" class="btn btn-primary text-white rounded-3">
                Volver al inicio
            </a>
        </div>
    </main>
</body>
</html>
