<?php

namespace App\Http\Controllers;

use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Membership;
use App\Models\Orders;
use App\Models\Transaction_history;
use App\Http\Resources\ApiResponseResources;

class PaymentController extends Controller
{
    public function payMembership($id)
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $membership = Membership::findOrFail($id);
        $transactionHistory = Transaction_history::where('invoice_number', $membership->invoice_number)->first();

        if (!$transactionHistory) {
            if ($membership->type == 'individu') {
                $params = [
                    'transaction_details' => [
                        'order_id' => $membership->invoice_number,
                        'gross_amount' => 20000,
                    ],
                    'customer_details' => [
                        'first_name' => auth()->user()->name,
                        'email' => auth()->user()->email,
                    ],
                ];
            } elseif ($membership->type == 'usaha') {
                $params = [
                    'transaction_details' => [
                        'order_id' => $membership->invoice_number,
                        'gross_amount' => 30000,
                    ],
                    'customer_details' => [
                        'first_name' => auth()->user()->name,
                        'email' => auth()->user()->email,
                    ],
                ];
            }
    
             try {
                $paymentUrl = Snap::createTransaction($params)->redirect_url;
    
                return response()->json(['payment_url' => $paymentUrl]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        if ($transactionHistory->status == 'settlement') {
            return new ApiResponseResources(false, 'Anda Sudah Membayar Membership Ini!', null, 400);
        }

    }

    public function payProduct($id)
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $order= Orders::findOrFail($id);
        $transactionHistory = Transaction_history::where('invoice_number', $order->invoice_number)->first();

        if (!$transactionHistory) {
            $params = [
                'transaction_details' => [
                    'order_id' => $order->invoice_number,
                    'gross_amount' => $order->total_amount,
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                ],
            ];
    
             try {
                $paymentUrl = Snap::createTransaction($params)->redirect_url;
    
                return response()->json(['payment_url' => $paymentUrl]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        if ($transactionHistory->status == 'settlement') {
            return new ApiResponseResources(false, 'Anda Sudah Membayar Membership Ini!', null, 400);
        }

    }
}
