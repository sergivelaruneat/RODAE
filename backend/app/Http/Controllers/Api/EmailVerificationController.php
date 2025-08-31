<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    // GET /api/email/verify/{id}/{hash}
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->away(config('app.frontend_url').'/login?verified=0');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
            // Primera vez → redirige con éxito
            return redirect()->away(config('app.frontend_url').'/login?verified=1');
        }

        // Ya estaba verificado → también redirige al login con flag distinto
        return redirect()->away(config('app.frontend_url').'/login?already=1');
    }

    // POST /api/email/resend  (requiere JWT)
    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'El correo ya está verificado.']);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Correo de verificación enviado.']);
    }

    public function resendPublic(Request $request)
{
    $request->validate(['email' => 'required|email']);
    $user = \App\Models\User::where('email', $request->email)->first();

    if (! $user) {
        // Para no filtrar existencia de emails, devolvemos genérico
        return response()->json(['message' => 'Si existe la cuenta, se ha enviado la verificación.'], 200);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'El correo ya está verificado.'], 200);
    }

    $user->sendEmailVerificationNotification();
    return response()->json(['message' => 'Correo de verificación enviado.'], 200);
}
}
