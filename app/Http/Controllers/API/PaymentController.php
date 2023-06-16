<?php

namespace App\Http\Controllers\API;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user_id = $request->input('user_id');

        $payments = Payment::query();

        if ($user_id) {
            $payments->where('user_id', $user_id);
        }

        return ResponseFormatter::success(
            [
                'payments' => $payments->get(),
            ],
            'Bukti Pembayaran Ditemukan',
            200,
        );
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
            return ResponseFormatter::error('Bukti Pembayaran Gagal Dibuat', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return ResponseFormatter::error('Bukti Pembayaran Tidak Ditemukan', 404);
        }

        return ResponseFormatter::success([
            'payment' => $payment,
        ], 'Bukti Pembayaran Ditemukan', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|string|max:255',
        ]);

        $payment = Payment::find($id);

        if (!$payment) {
            return ResponseFormatter::error('Bukti Pembayaran Tidak Ditemukan', 404);
        }

        try {
            $payment = $payment->update([
                'status' => $request->input('status'),
            ]);

            $payment = Payment::find($id);

            // Change user type
            if ($payment->status == 'Valid') {
                User::find($payment->user_id)->update([
                    'type' => 'premium',
                ]);
            }

            return ResponseFormatter::success([
                'payment' => $payment,
            ], 'Status Bukti Pembayaran Berhasil Diubah', 200);
        } catch (Exception $error) {
            return ResponseFormatter::error('Status Bukti Pembayaran Gagal Diubah', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return ResponseFormatter::error('Bukti Pembayaran Tidak Ditemukan', 404);
        }

        $payment->delete();

        return ResponseFormatter::success(
            $payment->id,
            'Bukti Pembayaran Berhasil Dihapus',
            200
        );
    }
}
