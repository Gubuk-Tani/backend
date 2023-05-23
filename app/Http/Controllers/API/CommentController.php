<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $article_id)
    {
        $user_id = $request->input('user_id');
        $limit = $request->input('limit', 10);

        $comments = Comment::query();
        $comments->where('article_id', $article_id);

        if ($user_id) {
            $comments->where('user_id', $user_id);
        }

        $comments->with(['user'])->latest();

        return ResponseFormatter::success(
            $comments->paginate($limit),
            'Komentar Ditemukan',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $article_id)
    {
        $request->validate([
            'comment' => 'required|string|max:2200',
        ]);

        $comment = Comment::create([
            'comment' => $request->input('comment'),
            'article_id' => $article_id,
            'user_id' => Auth::user()->id,
        ]);

        $comment = Comment::with(['user'])->find($comment->id);

        return ResponseFormatter::success([
            'comment' => $comment,
        ], 'Komentar Berhasil Ditambahkan', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $article_id, string $id)
    {
        $comment = Comment::with(['user'])->find($id);

        return ResponseFormatter::success([
            'comment' => $comment,
        ], 'Komentar Ditemukan', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $article_id, string $id)
    {
        $request->validate([
            'comment' => 'required|string|max:2200',
        ]);

        $comment = Comment::find($id);

        $comment->update([
            'comment' => $request->input('comment'),
        ]);

        $comment = Comment::with(['user'])->find($id);

        return ResponseFormatter::success([
            'comment' => $comment,
        ], 'Komentar Berhasil Diubah', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $article_id, string $id)
    {
        $comment = Comment::where('article_id', $article_id)->find($id);

        if (!$comment) {
            return ResponseFormatter::error('Komentar Tidak Ditemukan', 404);
        }

        $comment->delete();

        return ResponseFormatter::success(
            $comment->id,
            'Komentar Berhasil Dihapus',
            200
        );
    }
}
