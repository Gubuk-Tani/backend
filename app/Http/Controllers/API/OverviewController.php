<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Detection;
use App\Models\Disease;
use App\Models\Plant;
use Carbon\Carbon;

class OverviewController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->input('range');

        $count_users = User::query();
        $count_articles = Article::query();
        $count_diseases = Disease::query();
        $count_plants = Plant::query();
        $count_detections = Detection::query();

        if ($range) {
            $date = Carbon::today()->subDays($range);

            $count_users->where('created_at', '>=', $date)->get();
            $count_articles->where('created_at', '>=', $date)->get();
            $count_diseases->where('created_at', '>=', $date)->get();
            $count_plants->where('created_at', '>=', $date)->get();
            $count_detections->where('created_at', '>=', $date)->get();
        }

        $overview = [
            'count_users' => $count_users->count(),
            'count_articles' => $count_articles->count(),
            'count_diseases' => $count_diseases->count(),
            'count_plants' => $count_plants->count(),
            'count_detections' => $count_detections->count(),
        ];

        return ResponseFormatter::success($overview, 'Ringkasan Ditemukan', 200);
    }
}
