<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ApiResponseResources;
use App\Models\Vacancy;
use App\Models\Conditions;

class VacancyController extends Controller
{
    public function index()
    {
        $vacancy = Vacancy::all();

        if ($vacancy->isEmpty()) {
            return new ApiResponseResources(false, 'Tidak ada Lowongan Tersedia!');
        }

        return new ApiResponseResources(true, 'Berhasil Menampilkan Daftar Lowongan', $vacancy->load('condition'));
    }

    public function store(Request $request)
    {
        $messages = [
            'units.required' => 'Unit Wajib Diisi!',
            'units.in' => 'Unit Hanya Boleh Berupa :values',
            'position.required' => 'Posisi Wajib Diisi!',
            'location.required' => 'Lokasi Wajib Diisi!',
            'ex_date.required' => 'Tanggal Berakhir Wajib Diisi!',
            'ex_date.date' => 'Tanggal Berakhir Wajib Dengan Format YYYY-MM-DD!',
            'condition.required' => 'Syarat Wajib di Isi!',
            'condition.array' => 'Syarat Wajib Berupa Array!',
        ];

        $validator = Validator::make($request->all(), [
            'units' => 'required|in:Bank Sampah,Minimarket',
            'position' => 'required',
            'location' => 'required',
            'ex_date' => 'required|date',
            'description' => 'required',
            'condition' => 'required|array',
        ], $messages);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), Null, 422);
        }

        $vacancy = Vacancy::create([
            'units' => $request->units,
            'position' => $request->position,
            'location' => $request->location,
            'ex_date' => $request->ex_date,
            'description' => $request->description,
        ]);

        if (!$vacancy) {
            return new ApiResponseResources(false, 'Gagal Menyimpan Lowongan!', Null, 500);
        }

        foreach ($request->condition as $con) {
            Conditions::create([
                'condition' => $con,
                'vacancy_id' => $vacancy->id,
            ]);
        }

        return new ApiResponseResources(true, 'Berhasil Menyimpan Lowongan!', $vacancy->load('condition'), 201);
    }

    public function show($id)
    {
        $vacancy = Vacancy::find($id);

        if (!$vacancy) {
            return new ApiResponseResources(false, 'Lowongan Tidak Ditemukan', Null, 404);
        }

        return new ApiResponseResources(true, 'Lowongan Berhasil Ditampilkan!', $vacancy->load('condition'), 201);
    }

    public function update(Request $request, $id)
    {
        $vacancy = Vacancy::find($id);

        if (!$vacancy) {
            return new ApiResponseResources(false, 'Lowongan Tidak Ditemukan!', null, 404);
        }

        $messages = [
            'units.required' => 'Unit Wajib Diisi!',
            'units.in' => 'Unit Hanya Boleh Berupa :values',
            'position.required' => 'Posisi Wajib Diisi!',
            'location.required' => 'Lokasi Wajib Diisi!',
            'ex_date.required' => 'Tanggal Berakhir Wajib Diisi!',
            'ex_date.date' => 'Tanggal Berakhir Wajib Dengan Format YYYY-MM-DD!',
            'condition.required' => 'Syarat Wajib di Isi!',
            'condition.array' => 'Syarat Wajib Berupa Array!',
        ];

        $validator = Validator::make($request->all(), [
            'units' => 'required|in:Bank Sampah,Minimarket',
            'position' => 'required',
            'location' => 'required',
            'ex_date' => 'required|date',
            'description' => 'required',
            'condition' => 'required|array',
        ], $messages);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), Null, 422);
        }

        $vacancy->update([
            'units' => $request->units,
            'position' => $request->position,
            'location' => $request->location,
            'ex_date' => $request->ex_date,
            'description' => $request->description,
        ]);

        if (!$vacancy) {
            return new ApiResponseResources(false, 'Lowongan Gagal Diupdate!', null, 500);
        }

        Conditions::where('vacancy_id', $id)->delete();

        foreach ($request->condition as $con) {
            Conditions::create([
                'condition' => $con,
                'vacancy_id' => $vacancy->id,
            ]);
        }

        return new ApiResponseResources(true, 'Lowongan Berhasil Diupdate', $vacancy->load('condition'));
    }

    public function delete($id)
    {
        $vacancy = Vacancy::find($id);

        if (!$vacancy) {
            return new ApiResponseResources(false, 'Lowongan Tidak Ditemukan!', null, 404);
        }

        $vacancy->delete();

        return new ApiResponseResources(true, 'Berhasil Menghapus Data!');
    }
}
