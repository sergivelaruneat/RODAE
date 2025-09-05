<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicationResource;
use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Publications\StorePublicationRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PublicationController extends Controller
{
    use AuthorizesRequests;
    /** GET /api/publications/feed (auth:api) */
    public function feed(Request $request)
    {
        $user   = $request->user();
        $userId = $user->id;

        // IDs: a quién sigo + yo
        $followingIds   = $user->following()->pluck('users.id');
        $visibleUserIds = $followingIds->push($userId);

        $perPage = (int) $request->integer('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $paginator = Publication::with(['user.profile'])
            ->whereIn('user_id', $visibleUserIds)
            ->withCount('comments')                 
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return PublicationResource::collection($paginator);
    }

    /** GET /api/publications?user_id=... (auth:api) */
    public function index(Request $request)
    {
        $data = $request->validate([
            'user_id'  => ['required','integer','exists:users,id'],
            'per_page' => ['sometimes','integer','min:1','max:100'],
        ]);

        $perPage = (int) ($data['per_page'] ?? 10);

        $paginator = Publication::with(['user.profile'])
            ->where('user_id', $data['user_id'])
            ->withCount('comments')                 
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return PublicationResource::collection($paginator);
    }

    /** POST /api/publications (auth:api) */
    public function store(StorePublicationRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $path     = $request->file('media')->store('publications', 'public');
        $mediaUrl = Storage::disk('public')->url($path); // /storage/publications/...

        $publication = Publication::create([
            'user_id'   => $user->id,
            'title'     => $data['title'] ?? null,
            'sport'     => $data['sport'] ?? null,
            'content'   => $data['content'],
            'media_url' => $mediaUrl,
        ]);

        // incluir user.profile y comments_count para el banner del front
        $publication->load(['user.profile'])->loadCount('comments');

        return (new PublicationResource($publication))
            ->additional(['message' => 'Publication created'])
            ->response()
            ->setStatusCode(201);
    }

    /** DELETE /api/publications/{publication} (auth:api + owner) */
    public function destroy(Request $request, Publication $publication)
    {
        // Policy: user_id === auth()->id()
        $this->authorize('delete', $publication);
        $publication->delete();

        return response()->json([
            'message' => 'Publication deleted',
        ], 200);
    }

    /**
     * Helper: devuelve un PublicationResource consistente con counts.
     */
    private function singleResource(int $publicationId): PublicationResource
    {
        $pub = Publication::with('user')
            ->withCount('comments')                 
            ->findOrFail($publicationId);

        return new PublicationResource($pub);
    }
}
