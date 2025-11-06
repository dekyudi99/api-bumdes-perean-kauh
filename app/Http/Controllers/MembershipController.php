<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ApiResponseResources;
use App\Models\Membership;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PaymentController;

class MembershipController extends Controller
{
    public function store(Request $request)
    {
        $membership = Auth::user()->membership;

        if (!$membership) {
            $messages = [
                'type.required' => 'Tipe Wajib Diisi!',
                'type.in' => 'Tipe Hanya Boleh Bernilai :values',
            ];

            $validator = Validator::make($request->all(), [
                'type' => 'required|in:individu,usaha',
            ], $messages);

            if ($validator->fails()) {
                return new ApiResponseResources(false, $validator->errors(), null, 422);
            }

            $userId = Auth::id();

            $membership = Membership::create([
                'user_id' => $userId,
                'invoice_number' => 'INV-'.time(),
            ]);

            $paymentController = new PaymentController();

            $paymentResponse = $paymentController->payMembership($membership->id);

            $paymentData = json_decode($paymentResponse->getContent(), true);
            
            $paymentUrl = $paymentData['payment_url'] ?? null;

            return new ApiResponseResources(true, 'Pembelian Membership Berhasil Dibuat!', [$membership, $paymentUrl], 200);
        } else {
            if ($membership->is_active && $membership->expired_at && $membership->expired_at->isFuture()) {
                return new ApiResponseResources(false, 'Membership Anda Sudah Aktif!', null, 400);
            }
        }
    }
}
