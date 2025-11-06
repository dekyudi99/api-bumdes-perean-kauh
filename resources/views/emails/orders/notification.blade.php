@component('mail::message')
# Pesanan Baru Diterima

Halo Admin,

Ada pesanan baru dari **{{ $order->user->name }}**.

**Detail Pesanan:**
- Invoice: {{ $order->invoice_number }}
- Jumlah: {{ $order->quantity }}
- Total Harga: Rp {{ number_format($order->total_amount, 0, ',', '.') }}

Terima kasih,<br>
{{ config('app.name') }} 
@endcomponent
