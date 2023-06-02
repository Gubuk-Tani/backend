<?php

namespace App\Http\Controllers\API;

use App\Models\Label;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $plant_id)
    {
        $labels = Label::where('plant_id', $plant_id);

        return ResponseFormatter::success(
            [
                'labels' => $labels->get(),
            ],
            'Label Ditemukan',
            200,
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $plant_id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $label = Label::create([
            'name' => $request->input('name'),
            'plant_id' => $plant_id,
        ]);

        $label = Label::find($label->id);

        return ResponseFormatter::success([
            'label' => $label,
        ], 'Label Berhasil Ditambahkan', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $plant_id, string $id)
    {
        $label = Label::where('plant_id', $plant_id)->find($id);

        if (!$label) {
            return ResponseFormatter::error('Label Tidak Ditemukan', 404);
        }

        return ResponseFormatter::success([
            'label' => $label,
        ], 'Label Ditemukan', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $plant_id, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $label = Label::where('plant_id', $plant_id)->find($id);

        if (!$label) {
            return ResponseFormatter::error('Label Tidak Ditemukan', 404);
        }

        $label->update([
            'name' => $request->input('name'),
        ]);

        $label = Label::find($id);

        return ResponseFormatter::success([
            'label' => $label,
        ], 'Label Berhasil Diubah', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $plant_id, string $id)
    {
        $label = Label::where('plant_id', $plant_id)->find($id);

        if (!$label) {
            return ResponseFormatter::error('Label Tidak Ditemukan', 404);
        }

        $label->delete();

        return ResponseFormatter::success(
            $label->id,
            'Label Berhasil Dihapus',
            200
        );
    }
}
