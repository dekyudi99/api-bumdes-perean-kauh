@component('mail::message')
# Pembayaran Baru Diterima

Halo Admin,

Ada Pembayaran Masuk Dari **{{ $payment->email_customer }}**.

**Detail Pesanan:**
- Invoice: {{ $payment->invoice_number }}
- Channel: {{ $payment->channel }}
- Total Harga: Rp {{ number_format($payment->value, 0, ',', '.') }}

Terima kasih,<br>
{{ config('app.name') }} 
@endcomponent
