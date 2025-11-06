@component('mail::message')
# Pemanggilan Service Baru

Halo Admin Bank Sampah,

Ada pemanggilan service baru dari **{{ $callService->user->name }}**.

**Detail Pesanan:**
- Lokasi: {{ $callService->take_location }}
- Catatan: {{ $callService->additional_note }}
- Email: {{ $callService->user->email }}

@component('mail::button', ['url' => config('app.url') . '/api/callService/detail/' . $callService->id])
Lihat Pemanggilan Service
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent