<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ApiResponseResources;
use App\Models\Trash_sold;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TrashSoldController extends Controller
{
    public function index ()
    {
        $trashSold = Trash_sold::all();

        if (!$trashSold) {
            return new ApiResponseResources(false, 'Gagal Mendapatkan data Penjualan Sampah!', Null, 500);
        }

        return new ApiResponseResources(true, 'Berhasil Mengambil data Penjualan Sampah!', $trashSold);
    }

    public function store(Request $request)
    {
        $message = [
            'trash_type.required' => 'Tipe Sampah Wajib Diisi!',
            'trash_type.in' => 'Tips Sampah Hanya Boleh Berupa :values',
            'weight.required' => 'Berat Wajib Diisi!',
            'weight.numeric' => 'Berat Wajib Berupa Angka!',
            'weight.digits_between' => 'Berat Hanya Bisa 10 Digit Angka Saja!',
            'user_id.required' => 'User ID Wajib Diisi!',
            'user_id.exists' => 'User ID Tidak Terdaftar!',
        ];

        $validator = Validator::make($request->all(), [
            'trash_type' => 'required|in:kaleng,kertas,plastik',
            'weight' => 'required|numeric|digits_between:1,10',
            'user_id' => 'required|exists:users,id',
        ], $message);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), Null, 500);
        }

        $user = User::find($request->user_id);

        if (!$user->email_verified) {
            return new ApiResponseResources(false, 'User ini Belum Memverifikasi Email!', Null, 422);
        } elseif ($user->role != 'masyarakat') {
            return new ApiResponseResources(false, 'Hanya User dengan Role Masyarakat yang Bisa Menjual Sampah!', Null, 422);
        }

        $officer = Auth::id();

        $trashSold = Trash_sold::create([
            'trash_type' => $request->trash_type,
            'weight' => $request->weight,
            'user_id' => $request->user_id,
            'officer' => $officer,
        ]);

        if (!$trashSold) {
            return new ApiResponseResources(false, 'Gagal Menyimpan Penjualan Sampah!', Null, 500);
        }

        return new ApiResponseResources(true, 'Berhasil Menyimpan Penjualan Sampah!', $trashSold, 201);
    }

    public function show($id)
    {
        $trashSold = Trash_sold::find($id);

        if (!$trashSold) {
            return new ApiResponseResources(false, 'Data Penjualan Sampah Tidak Ditemukan!', Null, 404);
        }

        return new ApiResponseResources(true, 'Berhasil Mengambil Data Penjualan Sampah!', $trashSold);
    }

    public function update (Request $request, $id)
    {
        $trashSold = Trash_sold::find($id);

        if (!$trashSold) {
            return new ApiResponseResources(false, 'Data Penjualan Sampah Tidak Ada!', Null, 500);
        }

        $message = [
            'trash_type.required' => 'Tipe Sampah Wajib Diisi!',
            'trash_type.in' => 'Tips Sampah Hanya Boleh Berupa :values',
            'weight.required' => 'Berat Wajib Diisi!',
            'weight.numeric' => 'Berat Wajib Berupa Angka!',
            'weight.digits_between' => 'Berat Hanya Bisa 10 Digit Angka Saja!',
        ];

        $validator = Validator::make($request->all(), [
            'trash_type' => 'required|in:kaleng,kertas,plastik',
            'weight' => 'required|numeric|digits_between:1,10',
        ], $message);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), Null, 500);
        }

        $officer = Auth::id();

        $trashSold->update([
            'trash_type' => $request->trash_type,
            'weight' => $request->weight,
            'officer' => $officer,
        ]);

        if (!$trashSold) {
            return new ApiResponseResources(false, 'Gagal Mengupdate Data Penjualan Sampah!', Null, 500);
        }

        return new ApiResponseResources(true, 'Berhasil Update Penjualan Sampah!', $trashSold);
    }

    public function myTrashSold()
    {
        $userId = Auth::id();
        $trashSold = Trash_sold::where('user_id', $userId)->first();

        if (!$trashSold) {
            return new ApiResponseResources(false, 'Gagal Mendapatkan Data Penjualan Sampah Anda!', Null, 500);
        }

        return new ApiResponseResources(true, 'Berhasil Mendapatkan Data Penjualan Sampah Anda!', $trashSold);
    }
}
