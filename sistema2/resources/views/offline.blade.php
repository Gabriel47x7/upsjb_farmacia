<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ff0a41">
    <title>Sin conexión | FARM SJB</title>
    <style>
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            background: #f3f4f6;
            color: #323232;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        main {
            width: min(100%, 440px);
            padding: 36px 28px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 8px 30px rgba(50, 50, 50, .1);
        }
        .icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 18px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #ff0a41;
            color: #fff;
            font-size: 36px;
            font-weight: 700;
        }
        h1 { margin: 0 0 12px; font-size: 24px; }
        p { margin: 0 0 24px; color: #4b5563; line-height: 1.5; }
        a {
            display: inline-block;
            padding: 11px 18px;
            border-radius: 8px;
            background: #ff0a41;
            color: #fff;
            font-weight: 700;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <main>
        <div class="icon" aria-hidden="true">+</div>
        <h1>Estás sin conexión</h1>
        <p>No se pudo cargar esta página. Revisa tu conexión a internet e inténtalo nuevamente.</p>
        <a href="/">Volver al inicio</a>
    </main>
</body>
</html>
