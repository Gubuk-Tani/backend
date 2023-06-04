<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Plant;
use App\Models\Disease;
use App\Models\Setting;
use App\Models\Detection;
use Illuminate\Http\Request;
use GuzzleHttp\Psr7\Response;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;

class DetectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user_id = $request->input('user_id');
        $range = $request->input('range');
        $limit = $request->input('limit', 10);

        $detections = Detection::query();

        if ($range && $range == 'today') {
            $date = Carbon::today();

            $detections->where('created_at', '>=', $date);
        }

        if ($user_id) {
            $detections->where('user_id', $user_id);
        }

        $detections->latest()->get();

        $result = [];

        foreach ($detections as $detection) {
            $disease = $this->getDisease($detection);

            $result[] = [
                'detection' => $detection,
                'disease' => $disease,
            ];
        }

        return ResponseFormatter::success(
            $detections->paginate($limit),
            'Riwayat Deteksi Penyakit Ditemukan',
            200,
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|file',
            'plant_id' => 'required|string|max:255',
        ]);

        try {
            // Store images
            if ($request->hasFile('image')) {
                $image_path = '';
                $image_path = $request->file('image')->store('detection');
            }

            // Get ML Endpoint
            $ml_endpoint = Setting::where('key', 'ml_endpoint')->first();
            $ml_endpoint = $ml_endpoint->value;

            // Get Identity Token
            $token = Http::withHeaders([
                'Metadata-Flavor' => 'Google'
            ])->get(
                'http://metadata/computeMetadata/v1/instance/service-accounts/default/identity?audience=' . $ml_endpoint
            );

            // Get Plant
            $plant = Plant::find($request->input('plant_id'));
            $plant = strtolower($plant->name);

            // Detection
            $response = Http::async()->withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'bearer ' . $token->body(),
            ])->attach(
                'file',
                Storage::get($image_path),
                str_replace('/', '_', $image_path)
            )->retry(5)->post($ml_endpoint, [
                'plant' => $plant,
            ])->wait();

            $result = 'Error';
            $confidence = null;

            if ($response->object()) {
                $result = $response->object()->label;
                $confidence = $response->object()->confidence;
            }

            // Add Detection
            $detection = Detection::create([
                'image' => $image_path,
                'result' => $result,
                'confidence' => $confidence,
                'plant_id' => $request->input('plant_id'),
                'user_id' => Auth::user()->id,
            ]);

            $detection = Detection::find($detection->id);

            // Get Disease
            $disease = $this->getDisease($detection);

            return ResponseFormatter::success([
                'detection' => $detection,
                'disease' => $disease,
            ], 'Deteksi Penyakit Berhasil', 201);
        } catch (Exception $error) {
            ResponseFormatter::error('Ada yang Salah. Deteksi Penyakit Gagal.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $detection = Detection::find($id);

        if (!$detection) {
            return ResponseFormatter::error('Deteksi Penyakit Tidak Ditemukan', 404);
        }

        // Get Disease
        $disease = $this->getDisease($detection);

        return ResponseFormatter::success([
            'detection' => $detection,
            'disease' => $disease,
        ], 'Deteksi Penyakit Ditemukan', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $detection = Detection::find($id);

        if (!$detection) {
            return ResponseFormatter::error('Deteksi Penyakit Tidak Ditemukan', 404);
        }

        $detection->delete();

        return ResponseFormatter::success(
            $detection->id,
            'Deteksi Penyakit Berhasil Dihapus',
            200
        );
    }

    // Get Disease
    private function getDisease(Detection $detection)
    {
        $disease = Disease::query()
            ->join('disease_tags', 'disease_tags.disease_id', '=', 'diseases.id')
            ->join('tags', 'disease_tags.tag_id', '=', 'tags.id')
            ->where('tags.tag', $detection->result)
            ->with('article')
            ->first();

        return $disease;
    }
}
