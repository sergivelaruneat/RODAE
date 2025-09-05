<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comments\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Publication;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /** GET /api/publications/{publication}/comments */
    public function index(Request $request, Publication $publication)
    {
        $perPage = (int) $request->integer('per_page', 10);

        $paginator = $publication->comments()
            ->with('user:id,name,username')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        return response()->json($paginator);
    }

    /** POST /api/publications/{publication}/comments */
    public function store(StoreCommentRequest $request, Publication $publication)
    {
        $comment = Comment::create([
            'publication_id' => $publication->id,
            'user_id'        => $request->user()->id,
            'body'           => $request->validated()['body'],
        ])->load('user:id,name,username');

        return response()->json($comment, 201);
    }

    /** (Opcional) DELETE /api/comments/{comment} — solo dueño */
    public function destroy(Request $request, Comment $comment)
    {
        if ($comment->user_id !== $request->user()->id) {
            abort(403, 'Not allowed.');
        }
        $comment->delete();
        return response()->json(['message' => 'Comment deleted']);
    }
}
