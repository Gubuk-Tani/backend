<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Tag;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Disease;
use App\Models\ArticleTag;
use App\Models\ArticleImage;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\API\ArticleController;
use App\Models\DiseaseTag;

class DiseaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tag = $request->input('tag');
        $search = $request->input('search');
        $limit = $request->input('limit', 10);

        $diseases = Disease::query();

        // Find specific disease
        if ($tag) {
            $diseases
                ->join('articles', 'diseases.article_id', '=', 'articles.id')
                ->join('article_tags', 'articles.id', '=', 'article_tags.article_id')
                ->join('tags', 'article_tags.tag_id', '=', 'tags.id')
                ->where('tags.tag', 'like', '%' . $tag . '%');

            $diseases->with('article')->select('diseases.*')->get();

            return ResponseFormatter::success(
                [
                    'disease' => $diseases->first(),
                ],
                'Penyakit Berhasil Ditemukan',
                200
            );
        }

        // Search (optional)
        if ($search) {
            $diseases
                ->join('articles', 'diseases.article_id', '=', 'articles.id')
                ->join('article_tags', 'articles.id', '=', 'article_tags.article_id')
                ->join('tags', 'article_tags.tag_id', '=', 'tags.id')
                ->where('diseases.name', 'like', '%' . $search . '%')
                ->orWhere('diseases.description', 'like', '%' . $search . '%')
                ->orWhere('articles.title', 'like', '%' . $search . '%')
                ->orWhere('articles.content', 'like', '%' . $search . '%')
                ->orWhere('tags.tag', 'like', '%' . $search . '%');
        }

        $diseases->with('article')->select('diseases.*')->latest();

        return ResponseFormatter::success(
            $diseases->paginate($limit),
            'Daftar Penyakit Berhasil Ditemukan',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Add Article
        $article = null;

        if (
            $request->input('type') &&
            $request->input('title') &&
            $request->input('content')
        ) {
            $article = (new ArticleController)->addArticle($request);
        } else if ($request->input('article_id')) {
            $article = Article::find($request->input('article_id'));
        } else {
            $article = Article::find(1);
        }

        $request->validate([
            'disease.name' => 'required|string|max:255',
            'disease.description' => 'required|string|max:255',
            'disease.image' => 'nullable|file',
            'disease.tags' => 'nullable|string|max:255',
        ]);

        try {
            // Add Disease
            $disease = Disease::create([
                'name' => $request->input('disease.name'),
                'description' => $request->input('disease.description'),
                'article_id' => $article->id,
            ]);

            // Store image
            if ($request->hasFile('disease.image')) {
                $image_path = '';
                $image_path = $request->file('disease.image')->store('disease');

                Disease::find($disease->id)->update([
                    'image' => $image_path,
                ]);
            }

            $disease = Disease::with('article')->find($disease->id);

            // Tags
            $tags = explode(',', $request->input('disease.tags'));

            foreach ($tags as $item) {
                $tag = Tag::query();

                $item = trim($item);

                if (sizeof(Tag::where('tag', $item)->get()) == 0) {
                    $tag = $tag->create([
                        'tag' => $item,
                    ]);
                } else {
                    $tag = $tag->where('tag', $item)->first();
                }

                // Disease Tag
                DiseaseTag::create([
                    'tag_id' => $tag->id,
                    'disease_id' => $disease->id,
                ]);
            }

            return ResponseFormatter::success([
                'disease' => $disease,
            ], 'Penyakit Berhasil Dibuat', 201);
        } catch (Exception $error) {
            return ResponseFormatter::error('Penyakit Gagal Dibuat' . $error, 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $disease = Disease::with('article')->find($id);

        if (!$disease) {
            return ResponseFormatter::error('Penyakit Tidak Ditemukan', 404);
        }

        return ResponseFormatter::success([
            'disease' => $disease,
        ], 'Penyakit Berhasil Ditemukan', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'disease.name' => 'nullable|string|max:255',
            'disease.description' => 'nullable|string|max:255',
            'disease.image' => 'nullable|file',
            'disease.tags' => 'nullable|string|max:255',
        ]);

        $disease = Disease::find($id);

        if (!$disease) {
            return ResponseFormatter::error('Penyakit Tidak Ditemukan', 404);
        }

        try {
            $disease->update([
                'name' => $request->input('disease.name'),
                'description' => $request->input('disease.description'),
                'article_id' => $request->input('disease.article_id'),
            ]);

            // Store image
            if ($request->hasFile('disease.image')) {
                // Delete old avatar
                if ($disease->image) {
                    Storage::delete($disease->image);
                }

                $image_path = '';
                $image_path = $request->file('disease.image')->store('disease');

                $disease->update([
                    'image' => $image_path,
                ]);
            }

            // Update Tags
            $previousTags = DiseaseTag::where('disease_tags.disease_id', $disease->id);

            $previousTags->delete();

            $tags = explode(',', $request->input('disease.tags'));

            foreach ($tags as $item) {
                $tag = Tag::query();

                $item = trim($item);

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
                    'disease_id' => $disease->id,
                ]);
            }

            $disease = Disease::with('article')->find($id);

            return ResponseFormatter::success([
                'disease' => $disease,
            ], 'Penyakit Berhasil Diubah', 200);
        } catch (Exception $error) {
            return ResponseFormatter::error('Penyakit Gagal Diubah' . $error, 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $disease = Disease::find($id);

        if (!$disease) {
            return ResponseFormatter::error('Penyakit Tidak Ditemukan', 404);
        }

        try {
            // Delete image
            if ($disease->image) {
                Storage::delete($disease->image);
            }

            // Delete Desease
            $disease->delete();

            // Delete Article
            Comment::where('article_id', $disease->article_id)->delete();
            ArticleImage::where('article_id', $disease->article_id)->delete();
            ArticleTag::where('article_id', $disease->article_id)->delete();
            Article::find($disease->article_id)->delete();

            return ResponseFormatter::success(
                $disease->id,
                'Penyakit Berhasil Dihapus',
                200,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error('Penyakit Gagal Dihapus' . $error, 500);
        }
    }
}
