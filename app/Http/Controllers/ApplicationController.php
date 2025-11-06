<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\http\Resources\ApiResponseResources;
use App\Models\Application;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    public function store(Request $request)
    {
        $messages = [
            'vacancy_id.required' => 'ID Lowongan Wajib Diisi!',
            'vacancy_id.exists' => 'Lowongan Tidak Tersedia!',
            'email.required' => 'Email Wajib Diisi!',
            'email.email' => 'Format Email Salah!',
            'additional_note.required' => 'Catatan Tambahan Wajib Diisi!'
        ];

        $validator = Validator::make($request->all(), [
            'vacancy_id' => 'required|exists:vacancy,id',
            'no_telepon' => 'required',
            'email' => 'required|email',
            'additional_note' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), null, 422);
        }

        $userId = Auth::id();

        $application = Application::create([
            'vacancy_id' => $request->vacancy_id,
            'no_telepon' => $request->no_telepon,
            'email' => $request->email,
            'additional_note' => $request->additional_note,
            'user_id' => $userId,
        ]);

        if (!$application) {
            return new ApiResponseResources(false, 'Gagal menyimpan lamaran!', null, 500);
        }

        return new ApiResponseResources(true, 'Berhasil menyimpan Lamaran!', $application, 201);
    }
}
