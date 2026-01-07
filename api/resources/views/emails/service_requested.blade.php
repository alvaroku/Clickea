<!DOCTYPE html>
<html>
<head>
    <title>Nueva Solicitud de Servicio</title>
</head>
<body>
    <h1>Hola, {{ $requestedService->provider->name }}</h1>
    <p>Has recibido una nueva solicitud para el servicio: <strong>{{ $requestedService->service->name }}</strong></p>

    <h3>Detalles de la solicitud:</h3>
    <ul>
        <li><strong>Cliente:</strong> {{ $requestedService->client->name }}</li>
        <li><strong>Fecha:</strong> {{ $requestedService->date }}</li>
        <li><strong>Hora:</strong> {{ $requestedService->time }}</li>
        <li><strong>Ubicación:</strong> {{ $requestedService->location }}</li>
        <li><strong>Observaciones:</strong> {{ $requestedService->observations ?? 'Ninguna' }}</li>
    </ul>

    <p>Por favor, ingresa a la plataforma para cotizar este servicio.</p>

    <p>Saludos,<br>Equipo de {{ config('app.name') }}</p>
</body>
</html>
