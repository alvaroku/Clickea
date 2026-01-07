<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestMailController extends Controller
{
    /**
     * Send a simple raw test email to verify SMTP configuration.
     */
    public function sendTestMail(Request $request)
    {
        $to = $request->query('email', 'alvaro.ku@infotec.mx');

        try {
            Log::info("Iniciando prueba de envío de correo a: {$to}");

            Mail::raw('Esta es una prueba de configuración de correo desde Laravel Services App.', function ($message) use ($to) {
                $message->to($to)
                    ->subject('Prueba de Conectividad SMTP');
            });

            return response()->json([
                'status' => 'success',
                'message' => "Correo de prueba enviado exitosamente a {$to}. Revisa tu bandeja de entrada o spam.",
                'config' => [
                    'mailer' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'encryption' => config('mail.mailers.smtp.encryption'),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error en TestMailController: " . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error al intentar enviar el correo.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
}
