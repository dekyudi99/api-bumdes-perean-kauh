<?php
namespace App\Http\Controllers;

use App\Models\Membership;
use Midtrans\Config;
use Midtrans\Notification;
use App\Http\Resources\ApiResponseResources;
use App\Models\Transaction_history;
use App\Models\Orders;
use App\Mail\PaymentNotificationMail;
use Illuminate\Support\Facades\Mail;

class MidtransCallbackController extends Controller
{
    public function handle()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION');

        try {
            $notif = new Notification();
        } catch (\Exception $e) {
            return new ApiResponseResources(false, 'Gagal Mendapatkan Notifikasi Dari Midtrans: '.$e, null, 400);
        }

        $membership = Membership::where('invoice_number', $notif->order_id)->first();
        $order = Orders::where('invoice_number', $notif->order_id)->first();

        if (!$membership) {
            if (!$order) {
                return new ApiResponseResources(false, 'Pesanan Tidak Ditemukan!', null, 404);
            } else {
                if ($notif->transaction_status == 'capture') {
                    if ($notif->fraud_status == 'accept') {
                        // Card payment berhasil
                        $order->update([
                            'status' => 'paid',
                        ]);
                    }
                } elseif ($notif->transaction_status == 'settlement') {
                    // Pembayaran berhasil (non-credit-card / selesai)
                    $order->update([
                        'status' => 'paid',
                    ]);
                } elseif ($notif->transaction_status == 'pending') {
                    return response()->json(['message' => 'Menunggu Pembayaran'], 200);
                } elseif (in_array($notif->transaction_status, ['cancel', 'expire', 'deny'])) {
                    return response()->json(['message' => 'Pembayaran gagal / dibatalkan'], 400);
                }

                // Simpan riwayat transaksi
                $transaction_history = Transaction_history::create([
                    'date' => now(),
                    'invoice_number' => $notif->order_id,
                    'channel' => $notif->payment_type,
                    'status' => $notif->transaction_status,
                    'value' => $notif->gross_amount,
                    'email_customer' => $notif->customer_details->email ?? $order->user->email ?? null,
                ]);

                $adminEmails = \App\Models\User::where('role', 'adminMinimarket')->pluck('email');
                Mail::to($adminEmails)->send(new PaymentNotificationMail($transaction_history));

                return response()->json(['message' => 'Callback processed successfully'], 200);
            }
            return new ApiResponseResources(false, 'Membership Tidak Ditemukan!', null, 404);
        }

        if ($membership->is_active) {
            return new ApiResponseResources(false, 'Membership Anda Sudah Aktif!');
        }

        if ($notif->transaction_status == 'capture') {
            if ($notif->fraud_status == 'accept') {
                // Card payment berhasil
                $membership->update([
                    'is_active' => true,
                    'expired_at' => now()->addDays(30),
                ]);
            }
        } elseif ($notif->transaction_status == 'settlement') {
            // Pembayaran berhasil (non-credit-card / selesai)
            $membership->update([
                'is_active' => true,
                'expired_at' => now()->addDays(30),
            ]);
        } elseif ($notif->transaction_status == 'pending') {
            return response()->json(['message' => 'Menunggu Pembayaran'], 200);
        } elseif (in_array($notif->transaction_status, ['cancel', 'expire', 'deny'])) {
            return response()->json(['message' => 'Pembayaran gagal / dibatalkan'], 400);
        }

        // Simpan riwayat transaksi
        $transaction_history = Transaction_history::create([
            'date' => now(),
            'invoice_number' => $notif->order_id,
            'channel' => $notif->payment_type,
            'status' => $notif->transaction_status,
            'value' => $notif->gross_amount,
            'email_customer' => $notif->customer_details->email ?? $membership->user->email ?? null,
        ]);

        $adminEmails = \App\Models\User::where('role', 'adminBankSampah')->pluck('email');
        Mail::to($adminEmails)->send(new PaymentNotificationMail($transaction_history));

        return response()->json(['message' => 'Callback processed successfully'], 200);
    }
}