<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResponseResources;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    public function index()
    {
        $article = Article::all();

        if ($article->isEmpty()) {
            return new ApiResponseResources(false, 'Tidak Ada Artikel!', null, 404);
        }

        return new ApiResponseResources(true, 'Berhasil Menampilkan List Artikel!', $article);
    }

    public function store(Request $request)
    {
        $messages = [
            'title.required' => 'Judul Wajib Diisi!',
            'description.required' => 'Deskripsi Wajib Diisi!',
            'image.required' => 'Gambar Wajib Diisi!',
            'image.image' => 'Format Gambar Salah',
            'image.mimes' => 'Format Gambar Wajib berupa jpeg,png,jpg,gif,svg|max:2048'
        ];

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ], $messages);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), null, 422);
        }

        $paths = $request->image->store('article', 'public');
        $userId = Auth::id();
        
        $article = Article::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $paths,
            'post_by' => $userId,

        ]);

        if (!$article) {
            return new ApiResponseResources(false, 'Gagal Menyimpan Artikel!', null, 500);
        }

        return new ApiResponseResources(true, 'Berhasil Menyimpan Artikel!', $article, 201);
    }

    public function show($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return new ApiResponseResources(false, 'Artikel Tidak Ditemukan', null, 404);
        }

        return new ApiResponseResources(true, 'Berhasil Manampilkan Artikel!', $article);
    }

    public function update(Request $request, $id)
    {
        $article = Article::find($id);

        if (!$article) {
            return new ApiResponseResources(false, 'Artikel Tidak Ditemukan', null, 404);
        }

        $messages = [
            'title.required' => 'Judul Wajib Diisi!',
            'description.required' => 'Deskripsi Wajib Diisi!',
            'image.required' => 'Gambar Wajib Diisi!',
            'image.image' => 'Format Gambar Salah',
            'image.mimes' => 'Format Gambar Wajib berupa jpeg,png,jpg,gif,svg|max:2048'
        ];

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ], $messages);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), null, 422);
        }
    }

    public function delete($id)
    {
        $article = Article::find($id);

        if (!$id) {
            return new ApiResponseResources(false, 'Artikel Tidak Ditemukan!', null, 404);
        }

        $article->delete();

        return new ApiResponseResources(true, 'Artikel Berhasil Dihapus!');
    }
}
