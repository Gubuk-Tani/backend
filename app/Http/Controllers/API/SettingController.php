<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Setting::query()->get();

        $settings = [];

        foreach ($data as $setting) {
            $settings[$setting['key']] = $setting['value'];
        }

        return ResponseFormatter::success(
            [
                'settings' => $settings,
            ],
            'Pengaturan Ditemukan',
            200,
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
            'key' => 'required|string|unique:settings,key',
            'value' => 'required',
        ]);

        $key = $request->input('key');
        $value = $request->input('value');

        $image_path = '';

        if ($request->hasFile('value')) {
            $image_path = $request->file('value')->store('public');
            $value = $image_path;
        }

        try {
            $setting = Setting::create([
                'key' => $key,
                'value' => $value,
            ]);

            // Get all settings
            $data = Setting::query()->get();

            $settings = [];

            foreach ($data as $setting) {
                $settings[$setting['key']] = $setting['value'];
            }

            return ResponseFormatter::success(
                [
                    'settings' => $settings,
                ],
                'Pengaturan Berhasil Ditambahkan',
                201
            );
        } catch (Exception $error) {
            return ResponseFormatter::error('Pengaturan Gagal Ditambahkan', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $setting = Setting::find($id);

        if (!$setting) {
            return ResponseFormatter::error('Pengaturan Tidak Ditemukan', 404);
        }

        return ResponseFormatter::success(
            [
                'setting' => $setting,
            ],
            'Pengaturan Ditemukan'
        );
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
            'key' => 'required',
            'value' => 'required',
        ]);

        $key = $request->input('key');
        $value = $request->input('value');

        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return ResponseFormatter::error('Pengaturan Tidak Ditemukan. Pengaturan Gagal Diubah', 404);
        }

        if ($request->hasFile('value') && $key == 'app_logo') {
            $image_path = '';

            // Delete old image
            if ($setting->value) {
                Storage::delete($setting->value);
            }

            // Store image
            $file_name = $request->file('value')->getClientOriginalName();
            $image_path = $request->file('value')->storeAs('public', $file_name);
            $value = $image_path;
        }

        // Update setting
        Setting::where('key', $key)->update([
            'value' => $value,
        ]);

        // Get all settings
        $data = Setting::query()->get();

        $settings = [];

        foreach ($data as $setting) {
            $settings[$setting['key']] = $setting['value'];
        }

        return ResponseFormatter::success(
            [
                'settings' => $settings,
            ],
            'Pengaturan Berhasil Diubah',
            200,
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        // Checking user role
        if (Auth::user()->role != 'admin') {
            return ResponseFormatter::error('Anda Bukan Admin', 401);
        }

        $setting = Setting::where('key', $request->input('key'))->first();

        if (!$setting) {
            return ResponseFormatter::error('Pengaturan Tidak Ditemukan. Pengaturan Gagal Dihapus', 404);
        }

        $setting->delete();

        return ResponseFormatter::success(
            [
                'key' => $setting->key,
            ],
            'Pengaturan Berhasil Dihapus',
            200
        );
    }

    /**
     * Instantiate a new SettingController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['only' => ['store', 'update', 'destroy']]);
    }
}
