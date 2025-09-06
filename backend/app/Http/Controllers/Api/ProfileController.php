<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * GET /api/profile
     * Devuelve el perfil del usuario autenticado (vía JWT).
     */
    public function show(): ProfileResource
    {
        $user = Auth::user();

        return new ProfileResource(
            $user->profile()->firstOrFail()
        );
    }

    /**
     * GET /api/users/{user}/profile
     * Devuelve el perfil público de otro usuario (por ID con route model binding).
     */
    public function showUser(User $user): ProfileResource
    {
        return new ProfileResource(
            $user->profile()->firstOrFail()
        );
    }

    /**
     * PUT /api/profile
     * Actualiza el perfil del usuario autenticado.
     * - En User: name, role
     * - En Profile: bio, sport, birthdate, avatar (guardado en BBDD como Base64 + mime + size)
     */
    public function update(UpdateProfileRequest $request): ProfileResource|JsonResponse
    {
        $user = Auth::user();
        $profile = $user->profile()->firstOrFail();

        // Protección extra (aunque el profile viene de la relación del propio user)
        if ($profile->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // USERS: name / role
        if ($request->filled('name')) {
            $user->name = (string) $request->input('name');
        }
        $user->save();

        // PROFILES: bio / sport / birthdate / avatar (Base64 en BBDD)
        $data = $request->only(['bio', 'sport', 'birthdate']);

        if ($request->hasFile('avatar')) {
            $file  = $request->file('avatar');
            $bytes = @file_get_contents($file->getRealPath());

            if ($bytes === false) {
                return response()->json(['message' => 'No se pudo leer el archivo de avatar'], 422);
            }

            $data['avatar_b64']  = base64_encode($bytes);
            $data['avatar_mime'] = $file->getMimeType() ?? 'application/octet-stream';
            $data['avatar_size'] = (int) $file->getSize();
        }

        $profile->update($data);
        $user->refresh()->load('profile');
        return new ProfileResource($profile);
    }

    /**
     * GET /api/profile/avatar
     * Devuelve el avatar (binario) del usuario autenticado.
     */
    public function avatarSelf(): JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        $user = Auth::user();
        return $this->streamAvatar($user);
    }

    /**
     * GET /api/users/{user}/avatar
     * Devuelve el avatar (binario) de otro usuario.
     */
    public function avatarUser(User $user): JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        return $this->streamAvatar($user);
    }

    /**
     * Respuesta binaria del avatar desde BBDD (Base64) con ETag y caché.
     */
    private function streamAvatar(User $user): JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        $profile = $user->profile()->firstOrFail();

        if (!$profile->avatar_b64) {
            // Sin avatar: 204 No Content (podrías servir un placeholder estático si lo prefieres)
            return response()->noContent();
        }

        $etag = '"' . sha1($profile->avatar_b64) . '"';

        // 304 Not Modified si el cliente ya tiene el mismo ETag
        $ifNoneMatch = request()->headers->get('If-None-Match');
        if ($ifNoneMatch && trim($ifNoneMatch) === $etag) {
            return response('', 304)->header('ETag', $etag);
        }

        $bytes = base64_decode($profile->avatar_b64, true);
        if ($bytes === false) {
            return response()->json(['message' => 'Avatar corrupto'], 500);
        }

        return response($bytes, 200)
            ->header('Content-Type', $profile->avatar_mime ?: 'application/octet-stream')
            ->header('Content-Length', (string) ($profile->avatar_size ?? strlen($bytes)))
            ->header('Cache-Control', 'public, max-age=604800') // 7 días
            ->header('ETag', $etag);
    }
}
