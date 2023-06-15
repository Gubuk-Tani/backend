<?php

namespace App\Http\Controllers\API;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required',
            'image' => 'required|file',
            'notes' => 'required|string|max:255',
        ]);

        try {
            // Store images
            if ($request->hasFile('image')) {
                $image_path = $request->file('image')->store('payment');
            }

            $user = Auth::user();

            $payment = Payment::create([
                'user_id' => $user->id,
                'payment_method_id' => $request->input('payment_method_id'),
                'image' => $image_path,
                'notes' => $request->input('notes'),
                'status' => 'Waiting',
            ]);

            return ResponseFormatter::success([
                'payment' => $payment,
            ], 'Bukti Pembayaran Berhasil Ditambahkan', 201);
        } catch (Exception $error) {
            return ResponseFormatter::error('Bukti Pembayaran Gagal Dibuat' . $error, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }
}
