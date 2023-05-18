<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleImage;
use App\Models\ArticleTag;
use App\Models\Tag;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $type = $request->input('type');
        $page = $request->input('page');
        $limit = $request->input('limit', 10);

        $articles = Article::query();

        if ($type) {
            $articles->where('type', $type);
        }

        if ($search) {
            $articles
                ->join('article_tags', 'articles.id', '=', 'article_tags.article_id')
                ->join('tags', 'article_tags.tag_id', '=', 'tags.id')
                ->where('articles.title', 'like', '%' . $search . '%')
                ->orWhere('articles.content', 'like', '%' . $search . '%')
                ->orWhere('tags.tag', 'like', '%' . $search . '%');
        }

        $articles->with(['articleImages', 'tags'])->select('articles.*')->latest();

        return ResponseFormatter::success(
            $articles->paginate($limit),
            'Daftar Artikel Berhasil Ditemukan',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'title' => 'required|string',
            'content' => 'required|string',
            // 'images' => 'nullable',
            // 'images.*' => 'nullable|file',
            'tags' => 'nullable|string',
        ]);

        $user = Auth::user();

        try {
            $article = Article::query();

            $article = $article->create([
                'type' => $request->input('type'),
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'user_id' => $user->id,
            ]);

            // Images
            $images = [];

            // Store images
            if ($request->hasFile('images')) {
                $files = $request->allFiles('images');

                foreach ($files as $image) {
                    $image_path = '';
                    $image_path = $image->store('article');

                    $images[] = $image_path;
                }
            }

            // Add images to database
            foreach ($images as $image) {
                ArticleImage::create([
                    'image' => $image,
                    'article_id' => $article->id,
                ]);
            }

            // Tags

            $tags = explode(', ', $request->input('tags'));

            foreach ($tags as $item) {
                $tag = Tag::query();

                if (sizeof(Tag::where('tag', $item)->get()) == 0) {
                    $tag = $tag->create([
                        'tag' => $item,
                    ]);
                } else {
                    $tag = $tag->where('tag', $item)->first();
                }

                // Article Tag
                ArticleTag::create([
                    'tag_id' => $tag->id,
                    'article_id' => $article->id,
                ]);
            }

            return ResponseFormatter::success([
                'article' => Article::with(['articleImages', 'tags'])->find($article->id),
            ], 'Artikel Berhasil Dibuat', 201);
        } catch (Exception $error) {
            return ResponseFormatter::error('Artikel Gagal Dibuat' . $error, 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return ResponseFormatter::success([
            'article' => Article::with(['articleImages', 'tags'])->find($id),
        ], 'Artikel Berhasil Ditemukan', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        ArticleImage::where('article_id', $id)->delete();
        ArticleTag::where('article_id', $id)->delete();

        $article = Article::query();

        try {
            $article->find($id)->delete();

            return ResponseFormatter::success(
                null,
                'Artikel Berhasil Dihapus',
                200,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error('Artikel Gagal Dihapus' . $error, 500);
        }
    }
}
