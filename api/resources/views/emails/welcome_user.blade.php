<!DOCTYPE html>
<html>
<head>
    <title>Bienvenido a Services App</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">
        <h2 style="color: #FF385C;">¡Hola, {{ $user->name }}!</h2>
        <p>Has sido registrado como **{{ $user->role->name }}** en nuestra plataforma.</p>
        <p>Estas son tus credenciales de acceso:</p>
        <div style="background: #f8fafc; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Usuario:</strong> {{ $user->email }}</p>
            <p style="margin: 5px 0;"><strong>Contraseña:</strong> {{ $password }}</p>
        </div>
        <p>Te recomendamos cambiar tu contraseña una vez que inicies sesión.</p>
        <a href="{{ config('app.url') }}" style="display: inline-block; background: #FF385C; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Acceder a la plataforma</a>
        <p style="margin-top: 30px; font-size: 0.9em; color: #777;">Si no solicitaste este registro, por favor ignora este mensaje.</p>
    </div>
</body>
</html>
