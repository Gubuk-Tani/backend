<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Article;
use App\Models\Pesticide;
use App\Models\ArticleTag;
use App\Models\ArticleImage;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\API\ArticleController;
use App\Models\Comment;

class PesticideController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tag = $request->input('tag');
        $search = $request->input('search');
        $limit = $request->input('limit', 10);

        $pesticides = Pesticide::query();

        // Find specific pesticide
        if ($tag) {
            $pesticides
                ->join('articles', 'pesticides.article_id', '=', 'articles.id')
                ->join('article_tags', 'articles.id', '=', 'article_tags.article_id')
                ->join('tags', 'article_tags.tag_id', '=', 'tags.id')
                ->where('tags.tag', 'like', '%' . $tag . '%');

            $pesticides->with('article')->select('pesticides.*')->get();

            return ResponseFormatter::success(
                [
                    'pesticide' => $pesticides->first(),
                ],
                'Pestisida Berhasil Ditemukan',
                200
            );
        }

        // Search (optional)
        if ($search) {
            $pesticides
                ->join('articles', 'pesticides.article_id', '=', 'articles.id')
                ->join('article_tags', 'articles.id', '=', 'article_tags.article_id')
                ->join('tags', 'article_tags.tag_id', '=', 'tags.id')
                ->where('pesticides.name', 'like', '%' . $search . '%')
                ->orWhere('pesticides.description', 'like', '%' . $search . '%')
                ->orWhere('articles.title', 'like', '%' . $search . '%')
                ->orWhere('articles.content', 'like', '%' . $search . '%')
                ->orWhere('tags.tag', 'like', '%' . $search . '%');
        }

        $pesticides->with('article')->select('pesticides.*')->latest();

        return ResponseFormatter::success(
            $pesticides->paginate($limit),
            'Daftar Pestisida Berhasil Ditemukan',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Add Article
        $article = (new ArticleController)->addArticle($request);

        // Add Pesticide
        $request->validate([
            'pesticide.name' => 'required|string',
            'pesticide.description' => 'required|string',
            'pesticide.image' => 'nullable|file',
        ]);

        try {
            $pesticide = Pesticide::create([
                'name' => $request->input('pesticide.name'),
                'description' => $request->input('pesticide.description'),
                'article_id' => $article->id,
            ]);

            // Store image
            if ($request->hasFile('pesticide.image')) {
                $image_path = '';
                $image_path = $request->file('pesticide.image')->store('pesticide');

                Pesticide::find($pesticide->id)->update([
                    'image' => $image_path,
                ]);
            }

            $pesticide = Pesticide::with('article')->find($pesticide->id);

            return ResponseFormatter::success([
                'pesticide' => $pesticide,
            ], 'Pestisida Berhasil Dibuat', 201);
        } catch (Exception $error) {
            return ResponseFormatter::error('Pestisida Gagal Dibuat' . $error, 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pesticide = Pesticide::with('article')->find($id);

        if (!$pesticide) {
            return ResponseFormatter::error('Pestisida Tidak Ditemukan', 404);
        }

        return ResponseFormatter::success([
            'pesticide' => $pesticide,
        ], 'Pestisida Berhasil Ditemukan', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'image' => 'nullable|file',
        ]);

        $pesticide = Pesticide::find($id);

        if (!$pesticide) {
            return ResponseFormatter::error('Pestisida Tidak Ditemukan', 404);
        }

        try {
            $pesticide->update([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ]);

            // Store image
            if ($request->hasFile('image')) {
                // Delete old avatar
                if ($pesticide->image) {
                    Storage::delete($pesticide->image);
                }

                $image_path = '';
                $image_path = $request->file('image')->store('pesticide');

                $pesticide->update([
                    'image' => $image_path,
                ]);
            }

            $pesticide = Pesticide::with('article')->find($id);

            return ResponseFormatter::success([
                'pesticide' => $pesticide,
            ], 'Pestisida Berhasil Diubah', 200);
        } catch (Exception $error) {
            return ResponseFormatter::error('Pestisida Gagal Diubah' . $error, 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pesticide = Pesticide::find($id);

        if (!$pesticide) {
            return ResponseFormatter::error('Pestisida Tidak Ditemukan', 404);
        }

        try {
            // Delete image
            if ($pesticide->image) {
                Storage::delete($pesticide->image);
            }

            // Delete Pesticide
            $pesticide->delete();

            // Delete Article
            Comment::where('article_id', $pesticide->article_id)->delete();
            ArticleImage::where('article_id', $pesticide->article_id)->delete();
            ArticleTag::where('article_id', $pesticide->article_id)->delete();
            Article::find($pesticide->article_id)->delete();

            return ResponseFormatter::success(
                $pesticide->id,
                'Pestisida Berhasil Dihapus',
                200,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error('Pestisida Gagal Dihapus' . $error, 500);
        }
    }
}
