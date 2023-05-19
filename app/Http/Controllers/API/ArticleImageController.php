<?php

namespace App\Http\Controllers\API;

use App\Models\ArticleImage;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ArticleImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $article_id)
    {
        $limit = $request->input('limit', 10);

        $article_images = ArticleImage::query();
        $article_images->where('article_id', $article_id);

        $article_images->latest();

        return ResponseFormatter::success(
            $article_images->paginate($limit),
            'Gambar Artikel Ditemukan',
            200,
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $article_id)
    {
        $request->validate([
            'image' => 'required|file',
        ]);

        // Store images
        if ($request->hasFile('image')) {
            $image_path = $request->file('image')->store('article');
        }

        $article_image = ArticleImage::create([
            'image' => $image_path,
            'article_id' => $article_id,
        ]);

        return ResponseFormatter::success([
            'article_image' => $article_image,
        ], 'Gambar Artikel Berhasil Ditambahkan', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $article_id, string $id)
    {
        $article_image = ArticleImage::where('article_id', $article_id)->find($id);

        if (!$article_image) {
            return ResponseFormatter::error('Gambar Artikel Tidak Ditemukan', 404);
        }

        return ResponseFormatter::success([
            'article_image' => $article_image,
        ], 'Gambar Artikel Ditemukan', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $article_id, string $id)
    {
        $request->validate([
            'image' => 'required|file',
        ]);

        $article_image = ArticleImage::where('article_id', $article_id)->find($id);

        if (!$article_image) {
            return ResponseFormatter::error('Gambar Artikel Tidak Ditemukan', 404);
        }

        // Store images
        if ($request->hasFile('image')) {
            // Delete old image
            if ($article_image->image) {
                Storage::delete($article_image->image);
            }

            $image_path = $request->file('image')->store('article');
        }

        $article_image->update([
            'image' => $image_path,
            'article_id' => $article_id,
        ]);

        return ResponseFormatter::success([
            'article_image' => $article_image,
        ], 'Gambar Artikel Berhasil Diubah', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $article_id, string $id)
    {
        $article_image = ArticleImage::where('article_id', $article_id)->find($id);

        // Delete old image
        if ($article_image->image) {
            Storage::delete($article_image->image);
        }

        $article_image->delete();

        return ResponseFormatter::success([
            null,
        ], 'Gambar Artikel Berhasil Dihapus', 200);
    }
}
