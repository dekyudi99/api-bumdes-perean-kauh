<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Resources\ApiResponseResources;

class UserController extends Controller
{
    public function profile()
    {
        $user = User::find(Auth::id());

        return new ApiResponseResources(true, 'Berhasil Menampilkan Profile', $user);
    }
}
