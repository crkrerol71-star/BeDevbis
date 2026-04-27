<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayOrderRequest;
use App\Http\Resources\PaymentResource;
use App\Models\EscrowLog;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function pay(PayOrderRequest $request, $orderId): JsonResponse
    {
        try {
            $order = Order::findOrFail($orderId);

            // Check if user is the buyer
            if ($order->buyer_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin membayar order ini',
                    'errors' => [],
                ], 403);
            }

            // Check if order is pending payment
            if ($order->status !== 'pending_payment') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order harus dalam status pending_payment',
                    'errors' => [],
                ], 400);
            }

            // Create payment (v1: langsung success)
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total_price,
                'method' => $request->payment_method,
                'status' => 'success',
                'paid_at' => now(),
            ]);

            // Update order status
            $order->update(['status' => 'paid']);

            // Create escrow log
            EscrowLog::create([
                'order_id' => $order->id,
                'actor_id' => $request->user()->id,
                'action' => 'payment_received',
                'amount' => $order->total_price,
                'note' => 'Payment via ' . $request->payment_method,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses',
                'data' => new PaymentResource($payment),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan',
                'errors' => [],
            ], 404);
        }
    }
}
