<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ApiResponseResources;

class ReviewController extends Controller
{
    public function store(Request $request, $id)
    {
        $messages = [
            'rating.required' => 'Rating Wajib Diisi!',
            'rating.numeric' => 'Rating Wajib Berupa Angka!',
            'rating.min' => 'Rating Minimal 1!',
            'rating.max' => 'Rating Maksimal 5!',
        ];

        $validator = Validator::make($request->all(), [
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'nullable|string',
        ], $messages);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), null, 422);
        }

        $userId = Auth::id();
        $user = User::whereId($userId)->first();
        $productId = $id;

        $hasPurchased = $user->order()
            ->where('status', 'paid')
            ->whereHas('items', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->exists(); 

        if (!$hasPurchased) {
            return new ApiResponseResources(false, 'Anda Hanya Bisa Mereview Produk yang Sudah Anda Beli!', null, 403);
        }
        
        $alreadyReviewed = Review::where('user_id', $user->id)
                                ->where('product_id', $productId)
                                ->exists();

        if ($alreadyReviewed) {
            return new ApiResponseResources(false, 'Anda Sudah Pernah Mereview Produk Ini!', null, 409);
        }

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return new ApiResponseResources(true, 'Berhasil Membuat Review!', $review, 201);
    }

    public function update(Request $request, $id)
    {
        $messages = [
            'rating.required' => 'Rating Wajib Diisi!',
            'rating.numeric' => 'Rating Wajib Berupa Angka!',
            'rating.min' => 'Rating Minimal 1!',
            'rating.max' => 'Rating Maksimal 5!',
        ];

        $validator = Validator::make($request->all(), [
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'nullable|string',
        ], $messages);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), null, 422);
        }

        $userId = Auth::id();
        $review = Review::whereId($id)->first();

        if ($userId != $review->user_id) {
             return new ApiResponseResources(false, 'Anda Tidak Bisa Mengedit Review Orang Lain!', null, 409);
        }

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return new ApiResponseResources(true, 'Berhasil mengedit Review', $review);
    }
}
