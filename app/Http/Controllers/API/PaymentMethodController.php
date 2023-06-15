<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payment_methods = PaymentMethod::query();

        return ResponseFormatter::success(
            [
                'payment_methods' => $payment_methods->get(),
            ],
            'Metode Pembayaran Ditemukan',
            200,
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'method' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:255',
        ]);

        $payment_method = PaymentMethod::create([
            'method' => $request->input('method'),
            'name' => $request->input('name'),
            'number' => $request->input('number'),
        ]);

        $payment_method = PaymentMethod::find($payment_method->id);

        return ResponseFormatter::success([
            'payment_method' => $payment_method,
        ], 'Metode Pembayaran Berhasil Ditambahkan', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payment_method = PaymentMethod::find($id);

        if (!$payment_method) {
            return ResponseFormatter::error('Metode Pembayaran Tidak Ditemukan', 404);
        }

        return ResponseFormatter::success([
            'payment_$payment_method' => $payment_method,
        ], 'Metode Pembayaran Ditemukan', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'method' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:255',
        ]);

        $payment_method = PaymentMethod::find($id);

        if (!$payment_method) {
            return ResponseFormatter::error('Metode Pembayaran Tidak Ditemukan', 404);
        }

        $payment_method = $payment_method->update([
            'method' => $request->input('method'),
            'name' => $request->input('name'),
            'number' => $request->input('number'),
        ]);

        $payment_method = PaymentMethod::find($id);

        return ResponseFormatter::success([
            'payment_method' => $payment_method,
        ], 'Metode Pembayaran Berhasil Diubah', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $payment_method = PaymentMethod::find($id);

        if (!$payment_method) {
            return ResponseFormatter::error('Metode Pembayaran Tidak Ditemukan', 404);
        }

        $payment_method->delete();

        return ResponseFormatter::success(
            $payment_method->id,
            'Metode Pembayaran Berhasil Dihapus',
            200
        );
    }
}
