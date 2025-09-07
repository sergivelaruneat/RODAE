<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    /**
     * Devuelve la relación entre el usuario autenticado (me) y $user:
     * - follows:     si YO sigo a $user
     * - followed_by: si $user me sigue a MI
     * - mutual:      si es mutuo
     */
    public function relationship(User $user): JsonResponse
    {
        $me = auth()->user();

        $follows    = $me->following()->where('followed_id', $user->id)->exists();
        $followedBy = $user->following()->where('followed_id', $me->id)->exists();

        return response()->json([
            'follows'     => $follows,
            'followed_by' => $followedBy,
            'mutual'      => $follows && $followedBy,
        ]);
    }

    /**
     * Seguir a un usuario (idempotente).
     */
    public function follow(User $user): JsonResponse
    {
        $me = auth()->user();

        if ($me->id === $user->id) {
            return response()->json(['message' => 'No puedes seguirte a ti mismo.'], 422);
        }

        if (! $me->following()->where('followed_id', $user->id)->exists()) {
            $me->following()->attach($user->id);
        }

        return $this->relationship($user);
    }

    /**
     * Dejar de seguir a un usuario (idempotente).
     */
    public function unfollow(User $user): JsonResponse
    {
        $me = auth()->user();

        if ($me->id === $user->id) {
            return response()->json(['message' => 'No puedes dejar de seguirte a ti mismo.'], 422);
        }

        $me->following()->detach($user->id);

        return $this->relationship($user);
    }

    /**
     * Lista de usuarios a los que $user sigue (paginado).
     */
    public function following(User $user, Request $request): JsonResponse
    {
        $per = $request->integer('per_page', 30);

        $paginator = $user->following()
            ->with('profile:id,user_id,updated_at')
            ->select('users.id','users.name','users.email','users.role','users.username')
            ->orderBy('users.name')
            ->paginate($per);

        // Adaptamos cada item al formato que usa el front (UserCard)
        $paginator->getCollection()->transform(function ($u) {
            return [
                'id'                => $u->id,
                'name'              => $u->name,
                'email'             => $u->email,
                'role'              => $u->role,
                'username'          => $u->username,
                'avatar_updated_at' => optional($u->profile?->updated_at)->timestamp,
            ];
        });

        return response()->json($paginator);
    }

    /**
     * (Opcional) Lista de seguidores de $user (paginado).
     */
    public function followers(User $user, Request $request): JsonResponse
    {
        $per = $request->integer('per_page', 30);

        $paginator = $user->followers()
            ->with('profile:id,user_id,updated_at')
            ->select('users.id','users.name','users.email','users.role','users.username')
            ->orderBy('users.name')
            ->paginate($per);

        $paginator->getCollection()->transform(function ($u) {
            return [
                'id'                => $u->id,
                'name'              => $u->name,
                'email'             => $u->email,
                'role'              => $u->role,
                'username'          => $u->username,
                'avatar_updated_at' => optional($u->profile?->updated_at)->timestamp,
            ];
        });

        return response()->json($paginator);
    }
}
