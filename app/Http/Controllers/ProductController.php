<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Http\Resources\ApiResponseResources;
use Illuminate\Support\Facades\Validator;
use App\Models\Images_product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index ()
    {
        $product = Products::all();

        if ($product->isEmpty()) {
            return new ApiResponseResources(false, 'Gagal Menampilkan Product!', null, 422);
        }

        return new ApiResponseResources(true, 'Berhasil Menampilkan Product!', $product);
    }

    public function store(Request $request) {
        $messages = [
            'name.required' => 'Nama Produk Wajib Diisi!',
            'name.max' => 'Nama Produk Maksimal 255 Karakter!',
            'description.required' => 'Deskripsi Wajib Diisi!',
            'price.required' => 'Harga Wajib Diisi!',
            'price.numeric' => 'Harga Wajib Berupa Angka!',
            'stock.required' => 'Stock Wajib Diisi!',
            'stock.numeric' => 'Stock Wajib Berupa Angka!',
            'image.array' => 'Bidang gambar harus berupa array.',
            'image.max' => 'Anda hanya dapat mengunggah maksimal :max gambar.',
            'image.*.image' => 'File pada salah satu gambar harus berupa gambar (jpeg, png, jpg, gif, svg).',
            'image.*.mimes' => 'Format file gambar tidak valid. Hanya format :values yang diizinkan.',
            'image.*.max' => 'Ukuran file salah satu gambar tidak boleh melebihi :max kilobyte.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'description' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'image' => 'nullable|array|max:5',
            'image.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], $messages);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), null, 422);
        }

        $userId = Auth::id();
        
        $product = Products::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'user_id' => $userId,
        ]);

        if ($request->hasFile("image")) {
            foreach ($request->file('image') as $imageFile) {
                $path = $imageFile->store('products', 'public');

                Images_product::create([
                    'product_id' => $product->id,
                    'image' => $path,
                ]);
            }
        }

        if (!$product) {
            return new ApiResponseResources(false, 'Gagal Menyimpan Produk!', null, 422);
        }

        return new ApiResponseResources(true, 'Produk Berhasil Disimpan!', $product);
    }

    public function show($id)
    {
        $product = Products::find($id);

        if (!$product) {
            return new ApiResponseResources(false, 'Produk Tidak Ditemukan!', null, 404);
        }

        return new ApiResponseResources(true, 'Produk Ditemukan!', $product);
    }

    public function update(Request $request, $id)
    {
        $product = Products::find($id);

        if (!$product) {
            return new ApiResponseResources(false, 'Produk Tidak Ditemukan!', null, 404);
        }

        $messages = [
            'name.required' => 'Nama Produk Wajib Diisi!',
            'name.max' => 'Nama Produk Maksimal 255 Karakter!',
            'description.required' => 'Deskripsi Wajib Diisi!',
            'price.required' => 'Harga Wajib Diisi!',
            'price.numeric' => 'Harga Wajib Berupa Angka!',
            'stock.required' => 'Stock Wajib Diisi!',
            'stock.numeric' => 'Stock Wajib Berupa Angka!',
            'image.array' => 'Bidang gambar harus berupa array.',
            'image.max' => 'Anda hanya dapat mengunggah maksimal :max gambar.',
            'image.*.image' => 'File pada salah satu gambar harus berupa gambar (jpeg, png, jpg, gif, svg).',
            'image.*.mimes' => 'Format file gambar tidak valid. Hanya format :values yang diizinkan.',
            'image.*.max' => 'Ukuran file salah satu gambar tidak boleh melebihi :max kilobyte.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'description' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'image' => 'nullable|array|max:5',
            'image.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], $messages);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), null, 422);
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
        ]);

        if ($request->hasFile("image")) {
            foreach ($product->images as $oldImage) {
                Storage::disk('public')->delete($oldImage->image);
                $oldImage->delete();
            }

            foreach ($request->file('image') as $imageFile) {
                $path = $imageFile->store('products', 'public');
                Images_product::create([
                    'product_id' => $product->id,
                    'image' => $path,
                ]);
            }
        }

        if (!$product) {
            return new ApiResponseResources(false, 'Produk Gagal Diupdate!', null, 422);
        }

        $product->load('images');

        return new ApiResponseResources(true, 'Produk Berhasil Diupdate!', $product);
    }

    public function delete($id)
    {
        $product = Products::find($id);
        
        if (!$product) {
            return new ApiResponseResources(false, 'Produk Tidak Ditemukan!', null, 404);
        }

        $images = Images_product::where('product_id', $product->id)->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->image);
        }

        $product->delete();

        return new ApiResponseResources(true, 'Produk Berhasil Dihapus!', Null, 204);
    }
}
