<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResponseResources;
use App\Models\Call_service;
use Illuminate\Support\Facades\Auth;

class CallServiceController extends Controller
{
    public function store(Request $request) 
    {
        $message = [
            'take_location.required' => 'Lokasi Wajib Diisi!',
            'take_location.max' => 'Lokasi Maksimal 255 Karakter',
        ];

        $validator = Validator::make($request->all(), [
            'take_location' => 'required|max:255',
            'additional_note' => 'nullable',
        ], $message);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), Null, 422);
        }

        $userId = Auth::id();

        $callService = Call_service::create([
            'take_location' => $request->take_location,
            'additional_note' => $request->additional_note,
            'user_id' => $userId,
        ]);

        if (!$callService) {
            return new ApiResponseResources(false, 'Pemanggilan Service Gagal Dibuat!', Null, 500);
        }

        return new ApiResponseResources(true, 'Berhasil Membuat Pemanggilan Service', $callService, 201);
    }

    public function update(Request $request, $id)
    {
        $callService = Call_service::find($id);

        if (!$callService) {
            return new ApiResponseResources(false, 'Pemanggilan Service Tidak Ada!', Null, 404);
        }

        $message = [
            'take_location.required' => 'Lokasi Wajib Diisi!',
            'take_location.max' => 'Lokasi Maksimal 255 Karakter',
        ];

        $validator = Validator::make($request->all(), [
            'take_location' => 'required|max:255',
            'additional_note' => 'nullable',
        ], $message);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), Null, 422);
        }

        $callService->update([
            'take_location' => $request->take_location,
            'additional_note' => $request->additional_note,
        ]);

        if (!$callService) {
            return new ApiResponseResources(false, 'Gagal Update Pemanggilan Service!', Null, 500);
        }

        return new ApiResponseResources(true, 'Pemanggilan Service Berhasil Diupdate!', $callService, );
    }

    public function confirm(Request $request, $id)
    {
        $callService = Call_service::find($id);

        if (!$callService) {
            return new ApiResponseResources(false, 'Pemanggilan Service Tidak Ada!', Null, 404);
        }

        $message = [
            'status.required' => 'Status Wajib Diisi!',
            'status.max' => 'Status Maksimal 255 Karakter',
        ];

        $validator = Validator::make($request->all(), [
            'status' => 'required|max:255',
        ], $message);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), Null, 422);
        }

        $userId = Auth::id();

        $callService->update([
            'status' => $request->status,
            'officer_id' => $userId,
        ]);

        if (!$callService) {
            return new ApiResponseResources(false, 'Gagal Mengkonfirmasi Pemanggilan Service!', Null, 500);
        }

        return new ApiResponseResources(true, 'Berhasil Mengkonfirmasi Pemanggilan Service!', $callService);
    }

    public function cancel(Request $request, $id)
    {
        $callService = Call_service::find($id);

        if (!$callService) {
            return new ApiResponseResources(false, 'Pemanggilan Service Tidak Ada!', Null, 404);
        }

        $message = [
            'status.required' => 'Status Wajib Diisi!',
            'status.max' => 'Status Maksimal 255 Karakter',
        ];

        $validator = Validator::make($request->all(), [
            'status' => 'required|max:255',
        ], $message);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), Null, 422);
        }

        $callService->update([
            'status' => $request->status,
        ]);

        if (!$callService) {
            return new ApiResponseResources(false, 'Gagal Membatalkan Pemanggilan Service!', Null, 500);
        }

        return new ApiResponseResources(true, 'Berhasil Membatalkan Pemanggilan Service!', $callService);
    }
}
