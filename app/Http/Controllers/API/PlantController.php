<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Label;
use App\Models\Plant;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PlantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plants = Plant::query()->with('labels')->get();

        if (sizeof($plants) == 0) {
            return ResponseFormatter::error('Tanaman Tidak Ditemukan', 404);
        }

        return ResponseFormatter::success(
            [
                'plants' => $plants,
            ],
            'Tanaman Berhasil Ditemukan',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Checking user role
        if (Auth::user()->role != 'admin') {
            return ResponseFormatter::error('Anda Bukan Admin', 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|file',
            'status' => 'required|string|in:Active,Inactive',
        ]);

        $name = $request->input('name');
        $status = $request->input('status', 'Active');

        try {
            $plant = Plant::create([
                'name' => $name,
                'status' => $status,
            ]);

            // Store images
            if ($request->hasFile('image')) {
                $image_path = '';
                $image_path = $request->file('image')->store('plant');

                Plant::find($plant->id)->update([
                    'image' => $image_path,
                ]);
            }

            $plant = Plant::with('labels')->find($plant->id);

            return ResponseFormatter::success([
                'plant' => $plant,
            ], 'Tanaman Berhasil Dibuat', 201);
        } catch (Exception $error) {
            return ResponseFormatter::error('Tanaman Gagal Dibuat' . $error, 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $plant = Plant::with('labels')->find($id);

        if (!$plant) {
            return ResponseFormatter::error('Tanaman Tidak Ditemukan', 404);
        }

        return ResponseFormatter::success([
            'plant' => $plant,
        ], 'Tanaman Berhasil Ditemukan', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Checking user role
        if (Auth::user()->role != 'admin') {
            return ResponseFormatter::error('Anda Bukan Admin', 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|file',
            'status' => 'required|string|in:Active,Inactive',
        ]);

        $name = $request->input('name');
        $status = $request->input('status');

        $plant = Plant::find($id);

        if (!$plant) {
            return ResponseFormatter::error('Tanaman Tidak Ditemukan', 404);
        }

        try {
            $plant->update([
                'name' => $name,
                'status' => $status,
            ]);

            // Store images
            if ($request->hasFile('image')) {
                // Delete old image
                if ($plant->image) {
                    Storage::delete($plant->image);
                }

                $image_path = '';
                $image_path = $request->file('image')->store('plant');

                Plant::find($plant->id)->update([
                    'image' => $image_path,
                ]);
            }

            $plant = Plant::with('labels')->find($plant->id);

            return ResponseFormatter::success([
                'plant' => $plant,
            ], 'Tanaman Berhasil Diubah', 200);
        } catch (Exception $error) {
            return ResponseFormatter::error('Tanaman Gagal Diubah', 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Checking user role
        if (Auth::user()->role != 'admin') {
            return ResponseFormatter::error('Anda Bukan Admin', 401);
        }

        $plant = Plant::find($id);

        if (!$plant) {
            return ResponseFormatter::error('Tanaman Tidak Ditemukan', 404);
        }

        try {
            // Delete image
            if ($plant->image) {
                Storage::delete($plant->image);
            }

            // Delete Labels
            Label::where('plant_id', $id)->delete();

            // Delete Plant
            $plant->delete();

            return ResponseFormatter::success(
                $plant->id,
                'Tanaman Berhasil Dihapus',
                200,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error('Tanaman Gagal Dihapus' . $error, 500);
        }
    }
}
